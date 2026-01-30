<?php

class Connexion 
{
    private static  $attributConnexion = [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",        
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,        
    ];

    private static $pdo;

    /**
     * return a PDO object
     * @return PDO
     */
    public static function pdo(): PDO{ return self::$pdo;}
    /**
     * create a connection between the database and the device
     * @return void
     */
    public static function connect():void {   
        // Récupération des variables d'environnement depuis Docker
        $h = getenv('DB_HOST');
        $d = getenv('DB_NAME');
        $l = getenv('DB_USER');
        
        // Lecture du mot de passe depuis le fichier secret
        $passwordFile = getenv('DB_PASSWORD_FILE');
        $p = ($passwordFile && file_exists($passwordFile)) ? trim(file_get_contents($passwordFile)) : '';

        $t = self::$attributConnexion;
        $max_retries = 5;
        $attempts = 0;

        while ($attempts < $max_retries) {
            try {
                self::$pdo = new PDO("mysql:host=$h;dbname=$d", $l, $p, $t);
                return;
            } catch (PDOException $e) {
                $attempts++;
                if ($attempts >= $max_retries) {
                    error_log("Échec final de connexion : " . $e->getMessage());
                    exit("Erreur : " . $e->getMessage());
                }
                sleep(2); 
            }   
        }
    }
}
