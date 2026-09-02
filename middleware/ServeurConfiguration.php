<?php

namespace Koyok\democratia\middleware;

use Exception;
use Koyok\democratia\domain\Extension\SubjectChecker;
use Koyok\democratia\lib\CodeDeRetourApi;

final class ServeurConfiguration
{
    private bool $isInDeveloppment;

    private bool $isInProduction;

    private string $uri;

    private string $client;

    public function __construct()
    {
        $this->uri = $_SERVER['HTTP_HOST'];
        $this->client = $_SERVER['REMOTE_ADDR'];
    }

    public function Configure(): array
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        [$this->isInDeveloppment, $this->isInProduction] = ServeurConfiguration::EnvDetermination();
        if ($this->isInDeveloppment) {
            $this->uri = 'http://'.$this->uri;
            ini_set('display_errors', 0);
            error_reporting(E_ALL);
        } elseif ($this->isInProduction) {
            $this->uri = 'https://'.$this->uri;
            ServeurConfiguration::Https_Configuration();
            error_reporting(E_ERROR | E_PARSE);
        } else {
            throw new Exception('Paramètre de production invalide ou corrompu', CodeDeRetourApi::Malicious->value);
        }

        return [$this->uri, $this->client, $this->isInDeveloppment, $this->isInProduction];
    }

    public function JWTConfiguration(string $requete, string $requestMethod, ?array $body): mixed
    {
        $header = getallheaders();
        $jwtChecker = new JwtChecker($this->uri, $this->client);

        if (empty($header['Authorization'])) {
            if ($requete == '/dashboard') {
                if ($this->isInDeveloppment || $this->isInProduction) {
                    ServeurConfiguration::Dashboard($this->isInProduction);
                    exit;
                } else {
                    throw new Exception('Aucun acces', CodeDeRetourApi::Malicious->value);
                }
            } elseif ($requete == '/users/refresh' && $requestMethod == 'POST') {
                return $jwtChecker->GenerateKey($body[0]);

            } else {
                throw new Exception('Entête incorrect', CodeDeRetourApi::Unauthorized->value);
            }
        } else {
            if ($requete == '/users/login' && $requestMethod == 'POST') {
                $jwtChecker->arrayChecker[3] = new SubjectChecker($body[0]);
            }
            $jwtChecker->CheckJWT($header);

            return $jwtChecker->GetPayload()['sub'];
        }
    }

    private static function Https_Configuration(): void {}

    public static function Dashboard(bool $isInProd): void
    {
        ServeurConfiguration::AuthentificationPageGeneration();
        if ($isInProd) {
            ServeurConfiguration::TWOFAAuthentification();
        }
        $page = file_get_contents('/var/www/html/dashboard.html');
        echo $page;
        exit;

    }

    public static function EnvDetermination(): array
    {
        $environement = ServeurConfiguration::EnvScanning('ENVIRONNEMENT');

        return [$environement == 'developpment', $environement == 'production'];
    }

    private static function EnvScanning(string $nomEnv): string
    {
        return getenv($nomEnv);
    }

    private static function AuthentificationPageGeneration(): void {}

    private static function TWOFAAuthentification(): void {}
}
