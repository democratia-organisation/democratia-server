<?php

namespace Koyok\democratia\middleware;

use Exception;
use Koyok\democratia\lib\CodeDeRetourApi;
use Symfony\Component\Dotenv\Dotenv;

final class ServeurConfiguration
{
    public static function Configure(): array
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        $client = '';
        [$isInDeveloppment, $isInProduction] = ServeurConfiguration::EnvDetermination();
        if ($isInDeveloppment) {
            $uri = 'http://';
            ini_set('display_errors', 0);
            error_reporting(E_ALL);
        } elseif ($isInProduction) {
            $uri = 'https://';
            ServeurConfiguration::Https_Configuration();
            error_reporting(E_ERROR | E_PARSE);
        } else {
            throw new Exception('Paramètre de production invalide ou corrompu', CodeDeRetourApi::Malicious->value);
        }
        $uri .= $_SERVER['HTTP_HOST'];
        $client .= $_SERVER['REMOTE_ADDR'];

        return [$uri, $client, $isInDeveloppment, $isInProduction];
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
