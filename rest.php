<?php

// Activation de l'affichage des erreurs pour le débogage
error_reporting(E_ERROR | E_PARSE); 
ini_set('display_errors', 0); // On ne les affiche pas sur la page

// Nettoyage radical du buffer
while (ob_get_level()) ob_end_clean();

// On traite l'URI seulement si on est sur un serveur web (pas en ligne de commande)
if (php_sapi_name() !== 'cli') {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($uri !== '/rest.php' && $uri !== '/' && file_exists(__DIR__ . $uri)) {
        return false;
    }
}
// Ajouter les en-têtes CORS pour rendre l'API accessible par n'importe quel client
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

$requestMethod = $_SERVER['REQUEST_METHOD']; 
$requete = $_GET["request"];
$parameters = [];
if (isset($_GET["parameters"])) {
    // On décode d'abord l'URL (pour les @, :, etc.) puis le JSON
    $decodedJson = json_decode(urldecode($_GET["parameters"]), true);
    $parameters = is_array($decodedJson) ? $decodedJson : [];
}

error_log("METHOD: " . $requestMethod);
error_log("REQUETE: " . $requete);
error_log("PARAMS: " . print_r($parameters, true));

// 4. Verification de la requête
if (!isset($_GET["request"])) {
    echo json_encode(["success" => false, "message" => "Parametre 'request' manquant"],JSON_UNESCAPED_UNICODE);
    exit;
}
try {
    if (!file_exists("ClassRest.php")) {
        throw new Exception("Fichier ClassRest.php introuvable");
    }
    require_once "ClassRest.php";
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
                exit;
            }
            elseif ($requeteFinal == null){
                $api->verificationFormatage($parameters,$requete);
                $api->verificationBonneAction($requete,$test);
                $requeteFinal = $requete;
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
            $api->delete($parameters,$requeteFinal);
            break;
        default:
            throw new Exception("Aucune methode precise",CodeDeRetourApi::BadRequest->value);
    }
    $retour = $api->getTabRetour();
    if (empty($retour['data']) && $retour['success'] === true) {
        $retour['message'] = "Connexion réussie mais aucun résultat trouvé pour cette requête.";
    }
}       
catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error_type" => get_class($e),
        "message" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ],JSON_UNESCAPED_UNICODE);
    exit;
}


// Debug : si c'est vide, on force un message pour comprendre
if (empty($resultatFinal['data']) && $resultatFinal['success']) {
    error_log("DEBUG: La requête a réussi mais DATA est vide.");
}
$api->reponseApi();
$resultatFinal = $api->getTabRetour();
echo json_encode($resultatFinal, JSON_UNESCAPED_UNICODE); 
exit;