<?php

/**
 * Représente une instance de bucket
 */
final class Bucket
{
    private int $nombreBilles;

    public string $userFileName;

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
        $this->userFileName = './'.Bucket::$FOLDER_NAME.'/'.urlencode($mailUser).'.json';
    }

    public static function getRatio(string $mailUser): float
    {
        if (! new Bucket($mailUser)->MailFormatChecker()) {
            throw new Exception("Ce n'est pas un mail", CodeDeRetourApi::BadRequest->value);
        }

        return Bucket::deserialiser($mailUser)->calcul();
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

    public static function hasABucket(string $mailUser): bool
    {
        $bucket = new Bucket($mailUser);
        if (! $bucket->MailFormatChecker()) {
            throw new Exception("Ce n'est pas un mail", CodeDeRetourApi::BadRequest->value);
        }

        return file_exists($bucket->userFileName);
    }

    public function addRequest(): void
    {
        $this->nombreBilles += 1;
    }

    public static function serialiser(string $mailUser): bool
    {
        $bucket = new Bucket($mailUser);
        if (! $bucket->MailFormatChecker()) {
            throw new Exception("Ce n'est pas un mail", CodeDeRetourApi::BadRequest->value);
        }

        $tableau = [
            'nombreBilles' => $bucket->nombreBilles,
            'mailUser' => $bucket->mailUser,
        ];
        $nomDuFichier = Bucket::$FOLDER_NAME.'/'.urlencode($bucket->mailUser).'.json';
        $chaine = json_encode($tableau);
        $file = fopen($nomDuFichier, 'w');
        $value = fwrite($file, $chaine);

        return is_numeric($value);
    }

    public static function deserialiser(string $mailUser): Bucket
    {
        $bucket = new Bucket($mailUser);
        if (! $bucket->MailFormatChecker()) {
            throw new Exception("Ce n'est pas un mail", CodeDeRetourApi::BadRequest->value);
        }
        $file = fopen($bucket->userFileName, 'r');
        $value = $file == false ? null : fread($file, filesize($bucket->userFileName));
        $tableau = json_decode($value, true);

        return new Bucket($mailUser, $tableau['nombreBilles']);
    }

    private function MailFormatChecker(): bool
    {
        return filter_var($this->mailUser, FILTER_VALIDATE_EMAIL);

    }

    private function calcul(): float
    {
        return $this->nombreBilles;
    }

    public static function NettoyerBucket(): bool
    {
        $isDeserialize = true;
        while ($fichier = readdir(Bucket::$DIRECTORY) && $isDeserialize) {
            if ($fichier != '.' && $fichier != '..') {
                $bucket = Bucket::deserialiser($fichier);
                $bucket->nombreBilles = 0;
                $isDeserialize = $bucket->serialiser($bucket->mailUser);
            }
        }

        return $isDeserialize;
    }
}
