<?php

namespace Koyok\democratia\middleware;

use DateTime;
use Exception;
use Koyok\democratia\domain\Extension\SubjectChecker;
use Koyok\democratia\lib\CodeDeRetourApi;
use Symfony\Component\Dotenv\Dotenv;

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
            $uri = 'https://'.$this->uri;
            ServeurConfiguration::Https_Configuration();
            error_reporting(E_ERROR | E_PARSE);
        } else {
            throw new Exception('Paramètre de production invalide ou corrompu', CodeDeRetourApi::Malicious->value);
        }

        return [$this->uri, $this->client, $this->isInDeveloppment, $this->isInProduction];
    }

    public function JWTConfiguration(string $requete, string $requestMethod): mixed
    {
        $header = getallheaders();
        $jwtChecker = new JwtChecker($this->uri, $this->client);

        if (empty($header['Authorization'])) {
            if ($requete == 'dashboard') {
                if ($this->isInDeveloppment || $this->isInProduction) {
                    ServeurConfiguration::Dashboard($this->isInDeveloppment, $this->isInProduction);
                    exit;
                } else {
                    throw new Exception('Aucun acces', CodeDeRetourApi::Malicious->value);
                }
            } elseif ($requete == '/users/refresh' && $requestMethod == 'POST') {
                return $jwtChecker->GenerateKey($_POST[0]);

            } else {
                throw new Exception('Entête incorrect', CodeDeRetourApi::Unauthorized->value);
            }
        } else {
            if ($requete == '/users/login' && $requestMethod == 'POST') {
                $jwtChecker->arrayChecker[3] = new SubjectChecker($_POST[0]);
            }
            $jwtChecker->CheckJWT($header);

            return $jwtChecker->GetPayload()['sub'];
        }
    }

    public function BucketChecking(string $account): void
    {
        $bucket = Bucket::deserialiser($account);

        if (Bucket::hasABucket($account)) {
            $nombreBille = Bucket::getRatio($account);
            if ($bucket->getUserLimit()) {
                header('X-RateLimit-Reset: '.new DateTime()->getTimestamp() + Bucket::$tempNettoyage);
                header('Retry-After: 60');
                throw new Exception("Le nombre de requete par l'utilisateur a été atteint", CodeDeRetourApi::RateLimit->value);
            } else {
                $bucket->addRequest();
                header('X-RateLimit-Limit: '.Bucket::$MAXIMUM_BILLES_USER);
                header('X-RateLimit-Remaining: '.Bucket::$MAXIMUM_BILLES_USER - $bucket->nombreBilles);
            }
        } elseif (! Bucket::serialiser($account)) {
            throw new Exception('Error Processing Request', CodeDeRetourApi::InternalServerError->value);
        }
    }

    private static function Https_Configuration(): void {}

    public static function Dashboard(bool $isInDev, bool $isInProd): void
    {
        ServeurConfiguration::AuthentificationPageGeneration();
        if ($isInDev) {
            header('Location: index.html');
            exit;
        }
        if ($isInProd) {
            ServeurConfiguration::TWOFAAuthentification();
            header('Location: '); // TODO : adresse IP serveur
            exit;
        }

    }

    private static function EnvDetermination(): array
    {
        $environement = ServeurConfiguration::EnvScanning('ENVIRONNEMENT');

        return [$environement == 'developpment', $environement == 'production'];
    }

    private static function EnvScanning(string $nomEnv): string
    {
        $dotenv = new Dotenv;
        $dotenv->load(dirname(__DIR__, 1).'/.env');

        return $_ENV[$nomEnv];
    }

    private static function AuthentificationPageGeneration(): void {}

    private static function TWOFAAuthentification(): void {}
}
