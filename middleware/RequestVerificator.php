<?php

namespace Koyok\democratia\middleware;

use Exception;
use InvalidArgumentException;
use Koyok\democratia\lib;
use LogicException;

final class RequestVerificator
{
    public static function verificationFormatage(
        array $parameters,
        ?string $requete = null
    ): void {
        $nombreDeParametereDonne = count($parameters);
        $estNonPrepare = ! preg_match_all('/\?/', $requete) && ! preg_match_all('/\:/', $requete);
        if ($nombreDeParametereDonne > 0) {
            if ($estNonPrepare) {
                throw new InvalidArgumentException('Requete non prepare donc refuse', lib\CodeDeRetourApi::BadRequest->value);
            } else {
                $matches = [];
                if (preg_match_all('/\?/', $requete, $matches)) {
                    $nombreDeParametereRequete = count($matches[0]);
                } elseif (preg_match_all('/\:/', $requete, $matches)) {
                    $nombreDeParametereRequete = count($matches[0]);
                }
                if ($nombreDeParametereDonne != $nombreDeParametereRequete) {
                    throw new InvalidArgumentException('Nombre de parametre donnees differents des parametres de la requete', lib\CodeDeRetourApi::BadRequest->value);
                }
            }
        } else {
            if (! $estNonPrepare) {
                throw new InvalidArgumentException("Requete prepare alors qu'aucun parametre n'est donnee", lib\CodeDeRetourApi::BadRequest->value);
            }
        }
    }

    public static function verificationBonneAction(
        string $requete,
        string $actionAttendu
    ): void {
        $matches = [];
        if (! preg_match_all($actionAttendu, $requete, $matches)) {
            print_r($matches);
            echo "$actionAttendu $requete";
            throw new LogicException('Requete illogique au vu de src\la methode api utilise', lib\CodeDeRetourApi::BadRequest->value);
        }

    }

    public static function verificationValeurDonne(
        ?string $requete = null
    ): void {
        if (($requete == null)) {
            throw new InvalidArgumentException('Aucune requete ou fonction donnee', lib\CodeDeRetourApi::BadRequest->value);
        }
    }

    private function verificationTable($table): void
    {
        $tablesAutorisees = ['internaute', 'groupe'];
        if (! in_array($table, $tablesAutorisees)) {
            throw new InvalidArgumentException('Table non autorisee.', lib\CodeDeRetourApi::BadRequest->value);
        }
    }

    public function verifierPasDeRequeteSQL(string $requete): void
    {
        $enteteRequeteSQL = ['SELECT', 'UPDATE', 'DELETE', 'CREATE', 'DROP'];
        foreach ($enteteRequeteSQL as $key => $value) {
            if (str_contains($requete, $value)) {
                throw new Exception('Error Processing Request', 1);
            }
        }

    }

    private function verificationInjection(string $table): void
    {
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException('Nom de table invalide.', lib\CodeDeRetourApi::BadRequest->value);
        }
    }
}
