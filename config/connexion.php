<?php

class Connexion 
{
    static private $hostName = 'localhost';
    static private $database = 'saes3-mmarti32';
    static private $login = 'saes3-mmarti32';
    static private $password = 'JPwzfAbGq3EE+Exv';



    static private $attributConnexion = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",        
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        
        );

    static private $pdo;

    /**
     * return a PDO object
     * @return PDO
     */
    static public function pdo(): PDO{ return self::$pdo;}
    /**
     * create a connection between the database and the device
     * @return void
     */
    static public function connect():void {   
        $h = self::$hostName;
        $d = self::$database;
        $l = self::$login;
        $p = self::$password;
        $t = self::$attributConnexion;
        try {
            self::$pdo = new PDO("mysql:host =$h; dbname=$d",$l,$p,$t);
            
        } catch (PDOException $e) {
            try {
                $l = 'root';
                $p = 'Djonodo20050207/';
                self::$pdo = new PDO("mysql:host =$h; dbname=$d",$l,$p,$t);
            } catch (PDOException $eBis) {
                throw new PDOException("Erreur de la connexion au serveur" . " " . $eBis->getMessage(),CodeDeRetourApi::ServiceNonDisponible->value);
            }
            
        }
    }
}
