<?php

error_reporting(E_ERROR | E_PARSE); 
ini_set('display_errors', 0);

while (ob_get_level()) ob_end_clean();

if (php_sapi_name() !== 'cli') {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($uri !== '/rest.php' && $uri !== '/' && file_exists(__DIR__ . $uri)) {
        return false;
    }
}
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

$requestMethod = $_SERVER['REQUEST_METHOD']; 


$requeteRaw = $_GET["request"] ?? "";

if ($requeteRaw === null) {


    http_response_code(400);

    echo json_encode(["success" => false, "message" => "no parameters"]);

    exit;

}
$requete = $requeteRaw;

while (strpos($requete, '%') !== false) {

    $requete = urldecode($requete);

}
$requete = trim($requete);
$requete = preg_replace('/\s+$/', '', $requete);
$parameters = [];
if (isset($_GET["parameters"])) {
    $paramsRaw = $_GET["parameters"];

    if (strpos($paramsRaw, '%25') !== false) {
        $paramsRaw = urldecode($paramsRaw);
    }
    $decodedJson = json_decode(urldecode($_GET["parameters"]), true);
    $parameters = is_array($decodedJson) ? array_values($decodedJson) : [];
}

try {
    require_once "ClassRest.php";
    require_once "image_manager.php";
    $api = new Api();
    $api->verificationValeurDonne($requete);
    switch ($requestMethod) {
        case 'GET':
            $test = "/SELECT/i";
            $requeteFinal = $api->tryGetAction($requete,GetMethode::class);
            if ($requete=="getMethode") {
                http_response_code($api->getCode());
                $tableauRetourne = [
                    "success" => $api->isSuccess,
                    "message" => "Voici toutes les méthodes par défaults disponibles",
                    "data" => $api->getAvailableMethods()
                ];
                echo json_encode($tableauRetourne, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); 
                exit;
            }
            elseif ($requete=="obtenirImage") {
                GetGroupeImage($parameters[0]);
            }
            elseif ($requeteFinal == null){
                $api->verificationFormatage($parameters,$requete);
                $api->verificationBonneAction($requete,$test);
                $requeteFinal = $requete;
            }
            $api->get($parameters,$requeteFinal);
	    error_log($requeteFinal);
            break;
        case 'POST':
            $test = "/INSERT/i";
            $requeteFinal = $api->tryGetAction($requete,PostMethode::class);
            if ($requeteFinal == null){
                $api->verificationFormatage($parameters,$requete);
                $api->verificationBonneAction($requete,$test);
                $requeteFinal = $requete;
            }
            elseif ($requete=="publierImage") {
                UploadGroupeImage($parameters[0]);
            }
            $api->post($parameters,$requeteFinal);
            break;
        case 'PATCH':
            $test = "/UPDATE/i";
            $requeteFinal = $api->tryGetAction($requete,PatchMethode::class);
            if ($requeteFinal == null){
                $api->verificationFormatage($parameters,$requete);
                $api->verificationBonneAction($requete,$test);
                $requeteFinal = $requete;
            }
            $api->patch($parameters,$requeteFinal);
            break;
        case 'DELETE':
            $test = "/DELETE/i";
            $requeteFinal = $api->tryGetAction($requete,DeleteMethode::class);
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
    array_walk_recursive($retour, function (&$item) {
        if (is_string($item)) {
        
            $item = preg_replace('/[\x00-\x1F\x7F]/u', '', $item);
        }
    });
}       
catch (Throwable $e) {
    http_response_code($e->getCode());
    echo json_encode([
        "success" => false,
        "error_type" => get_class($e),
        "message" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ],JSON_UNESCAPED_UNICODE);
    exit;
}

$api->reponseApi();
$resultatFinal = $api->getTabRetour();
echo json_encode($resultatFinal, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); 
exit;
