<?php

/**
 * Représente une
 */
final class Bucket
{
    private int $nombreBilles;
    private int $duree;
    private string $mailUser;
    public static int $MAXIMUM_BILLES_USER = 1_000;
    public static int $MAXIMUM_BILLES_GLOBAL = 5_000_000;
    private static string $FOLDER_NAME = "bucket";


    private function __construct(string $mailUser, int $nombreBilles = 0, int $time) {
        $this->nombreBilles = $nombreBilles;
        $this->duree = $time; // changer le temps que si il est à une heure pile
        $this->mailUser = $mailUser;
    }
    
    public static function getRatio (string $mailUser) : float {
        $bucket = Bucket::deserialiser($mailUser);
        return $bucket->calcul();   
    }

    public static function getGlobalUsage() : float {
        $directory = opendir(Bucket::$FOLDER_NAME);
        $totalBucket = 0;
        while ($fichier = readdir($directory)) {
            if ($fichier != '.' && $fichier != '..') {
                $bucket = Bucket::deserialiser($fichier);
                $totalBucket += $bucket->nombreBilles;
            }
        }
        return $totalBucket;
    }

    public function serialiser() : bool {
        $tableau = [
            "nombreBilles" => $this->nombreBilles,
            "time" => $this->duree,
            "mailUser" => $this->mailUser,
        ];
        $nomDuFichier = Bucket::$FOLDER_NAME."/".urlencode($this->mailUser).".json";
        $chaine = json_encode($tableau);
        $file = fopen($nomDuFichier,'w');
        $value = fwrite($file,$chaine);
        return is_numeric($value);
    }

    private static function deserialiser(string $mailUser) : Bucket {
        $nomDuFichier = Bucket::$FOLDER_NAME."/".urlencode($mailUser).".json";
        $file = fopen($nomDuFichier,'r');
        $value = fread($file,filesize($nomDuFichier));
        $tableau = json_decode($value,true);
        return new Bucket($mailUser,$tableau["nombreBilles"],$tableau["time"]);
    }

    private function calcul() : float {
        return $this->nombreBilles / 3600;
    }
}
