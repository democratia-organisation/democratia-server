<?php

namespace Koyok\democratia\data\query;

use Exception;
use Koyok\democratia\data\config\Connexion;
use Koyok\democratia\lib;
use PDO;
use PDOException;
use RuntimeException;

// Appeler src\la methode correspondante de l'API

/**
 * Resume de Api : classe qui collection src\les methode api rest vers la base de donnee
 * toutes les actions avec cette api sont prepares, si votre requete n'a pas de parametre,
 * donnez un tableau de parametre vide pour executer, sinon cela bloquera
 */
class Api
{
    private lib\CodeDeRetourApi $retourApi;

    private int $codeDeRetourApi;

    private string $messageDeRetour;

    private mixed $valeurRetourne;

    private array $arrayRetour;

    public bool $isSuccess;

    public function __construct()
    {
        $this->retourApi = lib\CodeDeRetourApi::OK;
        $this->codeDeRetourApi = $this->retourApi->value;
        $this->messageDeRetour = 'Aucune action effectuee';
        $this->valeurRetourne = null;
        $this->isSuccess = true;
        $this->arrayRetour = [
            'success' => $this->isSuccess,
            'message' => $this->messageDeRetour,
            'data' => $this->valeurRetourne,
        ];
    }

    public function getAvailableMethods(): array
    {
        return [
            'POST' => $this->formatMethods(lib\PostMethode::cases()),
            'GET' => $this->formatMethods(lib\GetMethode::cases()),
            'PATCH' => $this->formatMethods(lib\PatchMethode::cases()),
            'DELETE' => $this->formatMethods(lib\DeleteMethode::cases()),
        ];
    }

    public function getCode(): int
    {
        return $this->codeDeRetourApi;
    }

    public function execute(array $parameters, string $requete): array
    {
        $pdo = $this->connexionBaseDeDonne();
        $this->valeurRetourne = $this->executeRequete($pdo, $requete, $parameters);
        if ($this->codeDeRetourApi == lib\CodeDeRetourApi::OK->value) {
            $this->messageDeRetour = 'Ressource obtenu avec success';
        }

        return $this->getTabRetour();

    }

    private function getTabRetour(): array
    {
        $this->arrayRetour['success'] = $this->isSuccess;
        $this->arrayRetour['message'] = $this->messageDeRetour;
        $this->arrayRetour['data'] = $this->valeurRetourne;
        $this->arrayRetour['code'] = $this->codeDeRetourApi;

        return $this->arrayRetour;
    }

    public function tryGetAction(string $requete, string $enumClass): ?string
    {
        foreach ($enumClass::cases() as $case) {
            if ($case->name === $requete) {
                return $case->value;
            }
        }

        return null;
    }

    // Fonction utilitaire pour formater src\les methodes
    private function formatMethods(array $cases): array
    {
        $formatted = [];
        foreach ($cases as $case) {
            $formatted[] = [
                'name' => $case->name,
                'value' => $case->value,
            ];
        }

        return $formatted;
    }

    private function connexionBaseDeDonne(): PDO
    {
        try {
            Connexion::connect();

            return Connexion::pdo();
        } catch (PDOException $e) {
            throw new Exception('Erreur de connexion à la base de donnees : '.$e->getMessage());
        }
    }

    public function getMessage(): string
    {
        return $this->messageDeRetour;
    }

    private function executeRequete(PDO $pdo, string $requete, array $parameters): mixed
    {
        try {
            // On force l'activation des erreurs PDO pour ce statement
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $requetePrepare = $pdo->prepare($requete);
            $executionReussi = $requetePrepare->execute($parameters);

            if (! $executionReussi) {
                throw new RuntimeException("L'exécution de la requête a échoué.");
            }

            $valeurRetourne = $requetePrepare->fetchAll(PDO::FETCH_ASSOC);

            // DEBUG : On écrit dans les logs Docker pour voir si on a trouvé Marie
            error_log('Lignes trouvées dans MySQL : '.count($valeurRetourne));

            return $valeurRetourne; // On renvoie TOUJOURS le tableau (vide ou plein)
        } catch (PDOException $e) {
            error_log('Erreur SQL : '.$e->getMessage());
            throw new Exception('Erreur SQL : '.$e->getMessage(), 500);
        }
    }

    public function setParametreErreur(int $codeDerreur,
        string $messageDeRetour): void
    {
        $this->messageDeRetour = $messageDeRetour;
        $this->retourApi = lib\CodeDeRetourApi::tryFrom($codeDerreur);
        $this->codeDeRetourApi = $codeDerreur;
        if ($this->retourApi != null) {
            $this->codeDeRetourApi = $this->retourApi->value;
        }

    }
}
