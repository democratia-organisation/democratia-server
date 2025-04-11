<?php

require_once "ClassRest.php";

// Ajouter les en-têtes CORS pour rendre l'API accessible par n'importe quel client
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");


if (!isset($_GET["requete"])) {
    $host  = $_SERVER['HTTP_HOST'];
    $extra = 'menu.html';
    if(preg_match("/projets.iut-orsay.fr/",$host)) {
        $host .= "\/saes3-mmarti32\/";
        $uri   = rtrim(dirname("/API/index.php"), '');
        header("Location: http://$host$uri/$extra");
        exit;
    }
    else{
        header("Location: http://$host/$extra");
        exit;
    }
    
}
$requestMethod = $_SERVER['REQUEST_METHOD']; 
$parameters = isset($_GET["parameters"]) ? json_decode($_GET["parameters"],true) :  [];
$requete = json_decode($_GET["requete"]);
try {
    $api = new Api();
    $api->verificationValeurDonne($requete);
    switch ($requestMethod) {
        case 'GET':
            $test = "/SELECT/i";
            $requeteFinal = $api->tryGetAction($requete,GetMethode::groupeUtilisatuer);
            if ($requete=="getMethode") {
                http_response_code($api->getCode());
                $tableauRetourne = [
                    "success" => $api->isSuccess,
                    "message" => "Voici toutes les méthodes par défaults disponibles",
                    "data" => $api->getAvailableMethods()
                ];
                http_response_code($api->getCode());
                echo json_encode($tableauRetourne);
                exit;
            }
            elseif ($requeteFinal == null){
                $api->verificationFormatage($parameters,$requete);
                $api->verificationBonneAction($requete,$test);
                $requeteFinal = $requete;
            }
            else {
                throw new InvalidArgumentException("Aucune requête ou methode specifiee.",CodeDeRetourApi::BadRequest->value);
            }
            $api->get($parameters,$requeteFinal);
            break;
        case 'POST':
            $test = "/INSERT/i";
            $requeteFinal = $api->tryGetAction($requete,PostMethode::CreerUtilisateur);
            if ($requeteFinal == null){
                $api->verificationFormatage($parameters,$requete);
                $api->verificationBonneAction($requete,$test);
                $requeteFinal = $requete;
            }
            else {
                throw new InvalidArgumentException("Aucune requête ou methode specifiee.",CodeDeRetourApi::BadRequest->value);
            }
            $api->post($parameters,$requeteFinal);
            break;
        case 'PATCH':
            $test = "/UPDATE/i";
            $requeteFinal = $api->tryGetAction($requete,PatchMethode::ModifInfoInternaute);
            if ($requeteFinal == null){
                $api->verificationFormatage($parameters,$requete);
                $api->verificationBonneAction($requete,$test);
                $requeteFinal = $requete;
            }
            else {
                throw new InvalidArgumentException("Aucune requête ou methode specifiee.",CodeDeRetourApi::BadRequest->value);
            }
            $api->patch($parameters,$requeteFinal);
            break;
        case 'DELETE':
            $test = "/DELETE/i";
            $requeteFinal = $api->tryGetAction($requete,DeleteMethode::supprimergroupe);
            if ($requeteFinal == null){
                $api->verificationFormatage($parameters,$requete);
                $api->verificationBonneAction($requete,$test);
                $requeteFinal = $requete;
            }
            else {
                throw new InvalidArgumentException("Aucune requête ou methode specifiee.",CodeDeRetourApi::BadRequest->value);
            }
            $api->delete($parameters,$requeteFinal);
            break;
        default:
            new Exception("Aucune methode precise",CodeDeRetourApi::BadRequest->value);
            break;
    } 
      
    }       
 catch (Exception $e) {
    $api->setParametreErreur($e->getCode(), $e->getMessage());
    $api->isSuccess = false;
    $api->reponseApi();
    
}
http_response_code($api->getCode());
echo json_encode($api->getTabRetour());

