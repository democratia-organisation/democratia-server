<?php

namespace Koyok\democratia;

use DateTime;
use Exception;
use Koyok\democratia\data\query\Api;
use Koyok\democratia\domain\Extension;
use Koyok\democratia\domain\utils;
use Koyok\democratia\domain\utils\ImageManager;
use Koyok\democratia\middleware\Bucket;
use Symfony\Component\Dotenv\Dotenv;
use Throwable;

require_once './vendor/autoload.php';

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

while (ob_get_level()) {
    ob_end_clean();
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requeteRaw = $_GET['request'] ?? '';
$dotenv = new Dotenv;
$dotenv->load(__DIR__.'/.env');
$isInDeveloppment = $_ENV['ENVIRONNEMENT'] == 'developpment';
$uri = $isInDeveloppment ? 'http://' : 'https://'; // TODO : ajouter le nécessaire pour du https
$uri .= $_SERVER['HTTP_HOST'];
$client = $_SERVER['REMOTE_ADDR'];

if ($requeteRaw === null) {
    http_response_code(utils\CodeDeRetourApi::BadRequest->value);
    echo json_encode(['success' => false, 'message' => 'no parameters']);
    exit;
}
$requete = $requeteRaw;

while (strpos($requete, '%') !== false) {
    $requete = urldecode($requete);
}

$requete = trim($requete);
$requete = preg_replace('/\s+$/', '', $requete);
$parameters = [];
if (isset($_GET['parameters'])) {
    $paramsRaw = $_GET['parameters'];

    if (strpos($paramsRaw, '%25') !== false) {
        $paramsRaw = urldecode($paramsRaw);
    }
    $decodedJson = json_decode(urldecode($_GET['parameters']), true);
    $parameters = is_array($decodedJson) ? array_values($decodedJson) : [];
}

try {
    $header = getallheaders();
    if (empty($header['Authorization']) && $requete != 'login') {
        if ($_SERVER['SCRIPT_NAME'] == '/index.php' && $_SERVER['REQUEST_URI'] == '/') {
            http_response_code(utils\CodeDeRetourApi::Redirected->value);
            header('Location: index.html');
            exit;
        } else {
            throw new Exception('Entête incorrect', utils\CodeDeRetourApi::Unauthorized->value);
        }
    }

    $jwtChecker = new middleware\JwtChecker($uri, $client, $header);
    if ($requete == 'login' && $requestMethod == 'GET') {
        $jwtChecker->GenerateKey($parameters[0]);
    } elseif (($requete == 'relogin' || $requete == 'SELECT * FROM internaute WHERE courriel=?') && $requestMethod == 'GET') {
        $jwtChecker->arrayChecker[3] = new Extension\SubjectChecker($email);
    }
    $jwtChecker->CheckJWT();
    $payload = $jwtChecker->GetPayload();
    $account = $payload['sub'];
    $bucket = Bucket::deserialiser($account);
    if (Bucket::hasABucket($account)) {
        $nombreBille = Bucket::getRatio($account);
        if ($nombreBille >= Bucket::$MAXIMUM_BILLES_USER) {
            header('X-RateLimit-Reset: '.new DateTime()->getTimestamp() + Bucket::$tempNettoyage);
            header('Retry-After: 60');
            throw new Exception("Le nombre de requete par l'utilisateur a été atteint", utils\CodeDeRetourApi::RateLimit->value);
        } else {
            $bucket->addRequest();
            header('X-RateLimit-Limit: '.Bucket::$MAXIMUM_BILLES_USER);
            header('X-RateLimit-Remaining: '.Bucket::$MAXIMUM_BILLES_USER - $bucket->nombreBilles);
        }
    } elseif (! Bucket::serialiser($account)) {
        throw new Exception('Error Processing Request', utils\CodeDeRetourApi::InternalServerError->value);
    }

    $api = new Api;
    middleware\Verificator::verificationValeurDonne($requete);
    switch ($requestMethod) {
        case 'GET':
            $test = '/SELECT/i';
            $requeteFinal = $api->tryGetAction($requete, utils\GetMethode::class);
            if ($requete == 'getMethode') {
                http_response_code($api->getCode());
                $tableauRetourne = [
                    'success' => $api->isSuccess,
                    'message' => 'Voici toutes les méthodes par défaults disponibles',
                    'data' => $api->getAvailableMethods(),
                ];
                echo json_encode($tableauRetourne, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                exit;
            } elseif ($requete == 'obtenirImage') {
                ImageManager::GetGroupeImage($parameters[0]);
            } elseif ($requete == 'relogin') {

                http_response_code($api->getCode());
                $result = $api->execute($parameters, 'SELECT * FROM internaute WHERE courriel=?');
                $resultatFinal = [
                    'success' => $api->isSuccess,
                    'message' => $api->getMessage(),
                    'data' => $result,
                ];
                echo json_encode($resultatFinal, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                exit;
            } elseif ($requeteFinal == null) {
                middleware\Verificator::verificationFormatage($parameters, $requete);
                middleware\Verificator::verificationBonneAction($requete, $test);
                $requeteFinal = $requete;
            }

            break;
        case 'POST':
            $test = '/INSERT/i';
            $requeteFinal = $api->tryGetAction($requete, utils\PostMethode::class);
            if ($requeteFinal == null) {
                middleware\Verificator::verificationFormatage($parameters, $requete);
                middleware\Verificator::verificationBonneAction($requete, $test);
                $requeteFinal = $requete;
            } elseif ($requete == 'publierImage') {
                ImageManager::UploadGroupeImage($parameters[0]);
            }

            break;
        case 'PATCH':
            $test = '/UPDATE/i';
            $requeteFinal = $api->tryGetAction($requete, utils\PatchMethode::class);
            if ($requeteFinal == null) {
                middleware\Verificator::verificationFormatage($parameters, $requete);
                middleware\Verificator::verificationBonneAction($requete, $test);
                $requeteFinal = $requete;
            }

            break;
        case 'DELETE':
            $test = '/DELETE/i';
            $requeteFinal = $api->tryGetAction($requete, utils\DeleteMethode::class);
            if ($requeteFinal == null) {
                middleware\Verificator::verificationFormatage($parameters, $requete);
                middleware\Verificator::verificationBonneAction($requete, $test);
                $requeteFinal = $requete;
            }

            break;
        default:
            throw new Exception('Aucune methode precise', utils\CodeDeRetourApi::BadRequest->value);
    }
    $retour = $api->execute($parameters, $requeteFinal);
    if (empty($retour['data']) && $retour['success'] === true) {
        $retour['message'] = 'Connexion réussie mais aucun résultat trouvé pour cette requête.';
    }
    array_walk_recursive($retour, function (&$item) {
        if (is_string($item)) {
            $item = preg_replace('/[\x00-\x1F\x7F]/u', '', $item);
            $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
        }
    });

} catch (Throwable $e) {
    $reponse = [
        'success' => false,
        'message' => 'Une erreur inattendu est survenu',
    ];
    if ($e->getCode() == utils\CodeDeRetourApi::Malicious->value) {
        $reponse['message'] = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    }
    if ($isInDeveloppment) {
        $reponse['file'] = $e->getFile();
        $reponse['line'] = $e->getLine();
        $reponse['error_type'] = $e->getMessage();
        $reponse['message'] = $e->getMessage();
        $reponse['stackTrace'] = $e->getTraceAsString();
    }
    http_response_code($e->getCode());
    echo json_encode($reponse, JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code($api->getCode());
echo json_encode($retour, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
exit;
