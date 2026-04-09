<?php

namespace Koyok\democratia\middleware;

use Exception;
use Koyok\democratia\domain\utils\CodeDeRetourApi;
use Symfony\Component\Dotenv\Dotenv;

final class ServeurConfiguration
{
    public static function Configure(): array
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        $dotenv = new Dotenv;
        $dotenv->load(dirname(__DIR__, 1).'/.env');
        $isInDeveloppment = $_ENV['ENVIRONNEMENT'] == 'developpment';
        $isInProduction = $_ENV['ENVIRONNEMENT'] == 'production';
        if ($isInDeveloppment) {
            $uri = 'http://';
            $client = 'http://';
            ini_set('display_errors', 0);
            error_reporting(E_ALL);
        } elseif ($isInProduction) {
            $uri = 'http://';
            $client = 'http://';
            ServeurConfiguration::Https_Configuration();
            error_reporting(E_ERROR | E_PARSE);
        } else {
            throw new Exception('Paramètre de production invalide ou corrompu', CodeDeRetourApi::Malicious->value);
        }
        $uri .= $_SERVER['HTTP_HOST'];
        $client = $_SERVER['REMOTE_ADDR'];

        return [$uri, $client, $isInDeveloppment, $isInProduction];
    }

    private static function Https_Configuration(): void {}
}
