<?php

namespace Koyok\democratia;

use DateTime;
use Exception;
use Koyok\democratia\data\query\Api;
use Koyok\democratia\domain\Extension;
use Koyok\democratia\domain\utils;
use Koyok\democratia\domain\utils\ImageManager;
use Throwable;

require_once './vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'];
[$uri,$client, $isInDeveloppment, $isInProduction] = middleware\ServeurConfiguration::Configure();
[$requete, $parameters] = middleware\Sanitizer::Sanitize();

$header = getallheaders();
$jwtChecker = new middleware\JwtChecker($uri, $client, $header);

if ($requete == 'login' && $requestMethod == 'GET') {
    $jwtChecker->GenerateKey($parameters[0]);
} elseif (($requete == 'relogin' || $requete == 'SELECT * FROM internaute WHERE courriel=?') && $requestMethod == 'GET') {
    $jwtChecker->arrayChecker[3] = new Extension\SubjectChecker($email);
}
$account = $jwtChecker->GetPayload($header)['sub'];

$test = '';
$methodeToCheck = '';
$api = new Api;

switch ($requestMethod) {
    case 'GET':
        $test = '/SELECT/i';
        $methodeToCheck = utils\GetMethode::class;
        break;
    case 'POST':
        $test = '/INSERT/i';
        $methodeToCheck = utils\PostMethode::class;
        break;
    case 'PATCH':
        $test = '/UPDATE/i';
        $methodeToCheck = utils\PatchMethode::class;
        break;
    case 'DELETE':
        $test = '/DELETE/i';
        $methodeToCheck = utils\DeleteMethode::class;
        break;
    default:
        throw new Exception("Méthode non prise en compte par l'api", utils\CodeDeRetourApi::BadRequest->value);
}

try {
    if (empty($header['Authorization']) && $requete != 'login') {
        if ($_SERVER['SCRIPT_NAME'] == '/index.php' && $_SERVER['REQUEST_URI'] == '/documentation') {
            http_response_code(utils\CodeDeRetourApi::Redirected->value);
            header('Location: index.html');
            exit;
        } else {
            throw new Exception('Entête incorrect', utils\CodeDeRetourApi::Unauthorized->value);
        }
    }
    $jwtChecker->CheckJWT();

    $bucket = middleware\Bucket::deserialiser($account);
    if (middleware\Bucket::hasABucket($account)) {
        $nombreBille = middleware\Bucket::getRatio($account);
        if ($nombreBille >= middleware\Bucket::$MAXIMUM_BILLES_USER) {
            header('X-RateLimit-Reset: '.new DateTime()->getTimestamp() + middleware\Bucket::$tempNettoyage);
            header('Retry-After: 60');
            throw new Exception("Le nombre de requete par l'utilisateur a été atteint", utils\CodeDeRetourApi::RateLimit->value);
        } else {
            $bucket->addRequest();
            header('X-RateLimit-Limit: '.middleware\Bucket::$MAXIMUM_BILLES_USER);
            header('X-RateLimit-Remaining: '.middleware\Bucket::$MAXIMUM_BILLES_USER - $bucket->nombreBilles);
        }
    } elseif (! middleware\Bucket::serialiser($account)) {
        throw new Exception('Error Processing Request', utils\CodeDeRetourApi::InternalServerError->value);
    }
    middleware\RequestVerificator::verificationValeurDonne($requete);
    middleware\RequestVerificator::verificationFormatage($parameters, $requete);
    switch ($requete) {
        case 'obtenirImage':
            $retour = ImageManager::GetGroupeImage($parameters[0]);
            break;
        case 'publierImage':
            $retour = ImageManager::UploadGroupeImage($parameters[0]);
            break;
        default:
            middleware\RequestVerificator::verificationBonneAction($requete, $test);
            $potentielAction = $api->tryGetAction($requete, $methodeToChekc);
            $requete = $potentielAction ?? $requete;
            $retour = $api->execute($parameters, $requete);
            break;
    }
} catch (Throwable $e) {
    $reponse = [
        'success' => false,
        'message' => 'Une erreur inattendu est survenu',
    ];
    if ($e->getCode() == utils\CodeDeRetourApi::Malicious->value && $isInProduction) {
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
    echo json_encode($reponse, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}
if (empty($retour['data']) && $retour['success'] === true) {
    $retour['message'] = 'Connexion réussie mais aucun résultat trouvé pour cette requête.';
    $retour['code'] = utils\CodeDeRetourApi::NoContent->value;
}
middleware\Sanitizer::PostSanitize($retour);
http_response_code($retour['code']);
echo json_encode($retour, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
exit;
