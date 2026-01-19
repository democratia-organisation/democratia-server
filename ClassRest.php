<?php

require_once "config/connexion.php";
require_once "CodeDeRetourApi.php";
require_once "ActionPossible.php";
// Appeler la methode correspondante de l'API

/**
 * 
 * Resume de Api : classe qui collection les methode api rest vers la base de donnee
 * toutes les actions avec cette api sont prepares, si votre requete n'a pas de parametre, 
 * donnez un tableau de parametre vide pour executer, sinon cela bloquera
 * 
 * 
 */


class Api
{
    private CodeDeRetourApi $retourApi;
    private int $codeDeRetourApi;
    private string $messageDeRetour;
    private mixed $valeurRetourne;
    private array $arrayRetour;
    public bool $isSuccess;

    public function __construct()
    {
        $this->retourApi = CodeDeRetourApi::OK;
        $this->codeDeRetourApi = $this->retourApi->value;
        $this->messageDeRetour = "Aucune action effectuee";
        $this->valeurRetourne = null;
        $this->isSuccess = true;
        $this->arrayRetour = [
            "success" => $this->isSuccess,
            "message" => $this->messageDeRetour,
            "data" => $this->valeurRetourne
        ];
    }
    public function getAvailableMethods(): array
    {
        return [
            "POST" => $this->formatMethods(PostMethode::cases()),
            "GET" => $this->formatMethods(GetMethode::cases()),
            "PATCH" => $this->formatMethods(PatchMethode::cases()),
            "DELETE" => $this->formatMethods(DeleteMethode::cases())
        ]; 
    }
    public function getCode() : int
    {
        return $this->codeDeRetourApi;
    }
    public function getMessage() : string
    {
        return $this->messageDeRetour;
    }
    public function getTabRetour() : array
    {
        return $this->arrayRetour;
    }

    

    public function post(array $parameters, string $requete): void
    {
        
        $this->execute($parameters,$requete);
    }

    public function patch(array $parameters, string $requete): void
    {
        $this->execute($parameters, $requete);
    }

    public function delete(array $parameters, string $requete): void
    {
        $this->execute($parameters, $requete);
    }

    public function get(array $parameters, string $requete): void
    {
        $this->execute($parameters, $requete);
    }

    private function execute(array $parameters, string $requete): void
    {
            $pdo = $this->connexionBaseDeDonne();
            $this->valeurRetourne = $this->executeRequete($pdo,$requete,$parameters);
            if ($this->codeDeRetourApi==CodeDeRetourApi::OK->value) $this->messageDeRetour = "Ressource obtenu avec success";
            $this->reponseApi();
        
    }

    public function reponseApi(): void
    {
        $this->arrayRetour["success"] = $this->isSuccess;
        $this->arrayRetour["message"] = $this->messageDeRetour;
        $this->arrayRetour["data"] = $this->valeurRetourne;
    }
    public function tryGetAction(string $requete,Methode $methode) : mixed
    {
        $i = 0;
        $tableauElement = $this->formatMethods($methode::cases());
        foreach ($tableauElement as $methode) {
            foreach ($methode as $key => $value) {
                if ($key == "name" && $key === $requete) {
                    return $methode::tryFrom($value);
                    
                }
            }
            
        }
        return null;
    }
    // Fonction utilitaire pour formater les methodes
    private function formatMethods(array $cases): array
    {
        $formatted = [];
        foreach ($cases as $case) {
            $formatted[] = [
                "name" => $case->name,
                "value" => $case->value
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
            throw new Exception("Erreur de connexion à la base de donnees : " . $e->getMessage());
        }
    }

    private function executeRequete(PDO $pdo, string $requete, array $parameters): mixed
    {
        try {
            // On force l'activation des erreurs PDO pour ce statement
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $requetePrepare = $pdo->prepare($requete);
            $executionReussi = $requetePrepare->execute($parameters);
    
            if (!$executionReussi) {
                throw new RuntimeException("L'exécution de la requête a échoué.");
            }
    
            $valeurRetourne = $requetePrepare->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG : On écrit dans les logs Docker pour voir si on a trouvé Marie
            error_log("Lignes trouvées dans MySQL : " . count($valeurRetourne));
            
            return $valeurRetourne; // On renvoie TOUJOURS le tableau (vide ou plein)
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Erreur SQL : " . $e->getMessage(), 500);
        }
    }
    public function setParametreErreur(int $codeDerreur, 
        string $messageDeRetour) : void
    {
        $this->messageDeRetour = $messageDeRetour;
        $this->retourApi = CodeDeRetourApi::tryFrom($codeDerreur);
        $this->codeDeRetourApi = $codeDerreur;
        if ($this->retourApi!=null) {
            $this->codeDeRetourApi = $this->retourApi->value;
            $this->reponseApi();
        }
        
    }
    public function verificationFormatage(
        array $parameters,
        ?string $requete = null
    ) : void
    {
        $nombreDeParametereDonne = count($parameters);
        $estNonPrepare = !preg_match_all('/\?/',$requete) && !preg_match_all('/\:/',$requete);
        if ($nombreDeParametereDonne>0) {
            if ($estNonPrepare) throw new InvalidArgumentException("Requete non prepare donc refuse",CodeDeRetourApi::BadRequest->value);
            else {
                $matches = [];
                if (preg_match_all('/\?/',$requete,$matches)) $nombreDeParametereRequete = count($matches[0]);
                elseif(preg_match_all('/\:/',$requete,$matches)) $nombreDeParametereRequete = count($matches[0]);
                if($nombreDeParametereDonne!=$nombreDeParametereRequete) throw new InvalidArgumentException("Nombre de parametre donnees differents des parametres de la requete",CodeDeRetourApi::BadRequest->value);
            }
        }
        else {
            if(!$estNonPrepare) throw new InvalidArgumentException("Requete prepare alors qu'aucun parametre n'est donnee",CodeDeRetourApi::BadRequest->value);
        }

        
    }
    public function verificationBonneAction(
        string $requete,
        string $actionAttendu
    ) : void
    {
        $matches = [];
        if (!preg_match_all($actionAttendu,$requete,$matches)) {print_r($matches);echo "$actionAttendu $requete";throw new LogicException("Requete illogique au vu de la methode api utilise", CodeDeRetourApi::BadRequest->value);};
        
    }
    public function verificationValeurDonne(
        ?string $requete = null
    ) : void
    {
        if (($requete == null)) throw new InvalidArgumentException("Aucune requete ou fonction donnee",CodeDeRetourApi::BadRequest->value);
    }
    private function verificationTable($table): void
    {
        $tablesAutorisees = ['internaute', 'groupe'];
        if (!in_array($table, $tablesAutorisees)) {
            throw new InvalidArgumentException("Table non autorisee.",CodeDeRetourApi::BadRequest->value);
        }
    }
    private function verificationInjection(string $table) : void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException("Nom de table invalide.",CodeDeRetourApi::BadRequest->value);
        }
    }

    
    
}


