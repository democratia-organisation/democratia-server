<?php

namespace Koyok\democratia\data\config;

use Koyok\democratia\middleware\ServeurConfiguration;
use PDO;
use Pdo\Mysql;
use PDOException;

final class Connexion
{
    private static $attributConnexion = [
        Mysql::ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    private static $pdo;

    /**
     * return a PDO object
     */
    public static function pdo(): PDO
    {
        return self::$pdo;
    }

    /**
     * create a connection between the database and the device
     */
    public static function connect(): void
    {
        $h = getenv('DB_HOST');
        $d = getenv('DB_NAME');
        $l = getenv('DB_USER');

        $passwordFile = getenv('DB_PASSWORD_FILE');
        $p = ($passwordFile && file_exists($passwordFile)) ? trim(file_get_contents($passwordFile)) : '';

        $t = self::$attributConnexion;
        $max_retries = 5;
        $attempts = 0;

        while ($attempts < $max_retries) {
            try {
                [$_, $isInProduction] = ServeurConfiguration::EnvDetermination();
                if ($isInProduction) {
                    $t[PDO::ATTR_PERSISTENT] = true;
                }
                self::$pdo = new PDO("mysql:host=$h;dbname=$d", $l, $p, $t);

                return;
            } catch (PDOException $e) {
                $attempts++;
                if ($attempts >= $max_retries) {
                    error_log('Échec final de connexion : '.$e->getMessage());
                    exit('Erreur : '.$e->getMessage());
                }
                sleep(1);
            }
        }
    }
}
