<?php

/**
 * Représente une instance de bucket
 */
final class Bucket
{
    private int $nombreBilles;

    private string $mailUser;

    public static int $MAXIMUM_BILLES_USER = 1_000;

    public static int $MAXIMUM_BILLES_GLOBAL = 5_000_000;

    private static string $FOLDER_NAME = 'bucket';

    private mixed $DIRECTORY;

    private function __construct(string $mailUser, int $nombreBilles = 0)
    {
        $this->nombreBilles = $nombreBilles;
        $this->mailUser = $mailUser;
        $this->DIRECTORY = opendir(Bucket::$FOLDER_NAME);
    }

    public static function getRatio(string $mailUser): float
    {
        $bucket = Bucket::deserialiser($mailUser);

        return $bucket->calcul();
    }

    public static function getGlobalUsage(): float
    {
        $totalBucket = 0;
        while ($fichier = readdir(Bucket::$DIRECTORY)) {
            if ($fichier != '.' && $fichier != '..') {
                $bucket = Bucket::deserialiser($fichier);
                $totalBucket += $bucket->nombreBilles;
            }
        }

        return $totalBucket;
    }

    public function serialiser(): bool
    {
        $tableau = [
            'nombreBilles' => $this->nombreBilles,
            'mailUser' => $this->mailUser,
        ];
        $nomDuFichier = Bucket::$FOLDER_NAME.'/'.urlencode($this->mailUser).'.json';
        $chaine = json_encode($tableau);
        $file = fopen($nomDuFichier, 'w');
        $value = fwrite($file, $chaine);

        return is_numeric($value);
    }

    private static function deserialiser(string $mailUser): Bucket
    {
        $nomDuFichier = Bucket::$FOLDER_NAME.'/'.urlencode($mailUser).'.json';
        $file = fopen($nomDuFichier, 'r');
        $value = fread($file, filesize($nomDuFichier));
        $tableau = json_decode($value, true);

        return new Bucket($mailUser, $tableau['nombreBilles']);
    }

    private function calcul(): float
    {
        return $this->nombreBilles;
    }

    public static function NettoyerBucket(): bool
    {
        $directory = opendir(Bucket::$FOLDER_NAME);
        $isDeserialize = true;
        while ($fichier = readdir(Bucket::$DIRECTORY) && $isDeserialize) {
            if ($fichier != '.' && $fichier != '..') {
                $bucket = Bucket::deserialiser($fichier);
                $bucket->nombreBilles = 0;
                $isDeserialize = $bucket->serialiser();
            }
        }

        return $isDeserialize;
    }
}
