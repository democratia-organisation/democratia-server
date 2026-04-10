<?php

namespace Koyok\democratia;

use DateTime;
use Exception;
use Koyok\democratia\data\query\Api;
use Koyok\democratia\domain\Extension;
use Throwable;

require_once './vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'];
[$uri,$client, $isInDeveloppment, $isInProduction] = middleware\ServeurConfiguration::Configure();
[$requete, $parameters, $error] = middleware\Sanitizer::Sanitize();

$test = '';
$methodeToCheck = '';
$api = new Api;

switch ($requestMethod) {
    case 'GET':
        $test = '/SELECT/i';
        $methodeToCheck = lib\GetMethode::class;
        break;
    case 'POST':
        $test = '/INSERT/i';
        $methodeToCheck = lib\PostMethode::class;
        break;
    case 'PATCH':
        $test = '/UPDATE/i';
        $methodeToCheck = lib\PatchMethode::class;
        break;
    case 'DELETE':
        $test = '/DELETE/i';
        $methodeToCheck = lib\DeleteMethode::class;
        break;
    default:
        throw new Exception("Méthode non prise en compte par l'api", lib\CodeDeRetourApi::BadRequest->value);
}

try {
    $header = getallheaders();
    if (! empty($error)) {
        throw new Exception($error['message'], $error['code']);
    }
    if (empty($header['Authorization']) && $requete != 'login') {
        if ($requete == 'dashboard') {
            if ($isInDeveloppment || $isInProduction) {
                middleware\ServeurConfiguration::Dashboard($isInDeveloppment, $isInProduction);
            } else {
                throw new Exception('Aucun acces', lib\CodeDeRetourApi::Malicious->value);
            }
            exit;
        } else {
            throw new Exception('Entête incorrect', lib\CodeDeRetourApi::Unauthorized->value);
        }
    }

    $jwtChecker = new middleware\JwtChecker($uri, $client);
    if ($requete == 'login' && $requestMethod == 'GET') {
        $retour = $jwtChecker->GenerateKey($parameters[0]);
        goto a;
    } elseif (($requete == 'relogin' || $requete == 'SELECT * FROM internaute WHERE courriel=?') && $requestMethod == 'GET') {
        $jwtChecker->arrayChecker[3] = new Extension\SubjectChecker($parameters[0]);
        $jwtChecker->CheckJWT($header);
    } else {
        $jwtChecker->CheckJWT($header);
    }

    $account = $jwtChecker->GetPayload()['sub'];

    $bucket = middleware\Bucket::deserialiser($account);
    if (middleware\Bucket::hasABucket($account)) {
        $nombreBille = middleware\Bucket::getRatio($account);
        if ($nombreBille >= middleware\Bucket::$MAXIMUM_BILLES_USER) {
            header('X-RateLimit-Reset: '.new DateTime()->getTimestamp() + middleware\Bucket::$tempNettoyage);
            header('Retry-After: 60');
            throw new Exception("Le nombre de requete par l'utilisateur a été atteint", lib\CodeDeRetourApi::RateLimit->value);
        } else {
            $bucket->addRequest();
            header('X-RateLimit-Limit: '.middleware\Bucket::$MAXIMUM_BILLES_USER);
            header('X-RateLimit-Remaining: '.middleware\Bucket::$MAXIMUM_BILLES_USER - $bucket->nombreBilles);
        }
    } elseif (! middleware\Bucket::serialiser($account)) {
        throw new Exception('Error Processing Request', lib\CodeDeRetourApi::InternalServerError->value);
    }

    middleware\RequestVerificator::verificationValeurDonne($requete);
    switch ($requete) {
        case 'obtenirImage':
            $retour = lib\ImageManager::GetGroupeImage($parameters[0]);
            break;
        case 'publierImage':
            $retour = lib\ImageManager::UploadGroupeImage($parameters[0]);
            break;
        default:
            middleware\RequestVerificator::verificationFormatage($parameters, $requete);
            middleware\RequestVerificator::verificationBonneAction($requete, $test);
            $potentielAction = $api->tryGetAction($requete, $methodeToCheck);
            $requete = $potentielAction ?? $requete;
            $retour = $api->execute($parameters, $requete);
            break;
    }

} catch (Throwable $e) {
    http_response_code($e->getCode());
    $reponse = [
        'success' => false,
        'message' => 'Une erreur inattendu est survenu',
    ];
    if ($e->getCode() == lib\CodeDeRetourApi::Malicious->value && $isInProduction) {
        header('Location: https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        exit;
    }
    if ($isInDeveloppment) {
        $reponse['file'] = $e->getFile();
        $reponse['line'] = $e->getLine();
        $reponse['error_type'] = $e->getMessage();
        $reponse['message'] = $e->getMessage();
        $reponse['stackTrace'] = $e->getTraceAsString();
    }
    echo json_encode($reponse, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}
if (empty($retour['data']) && $retour['success'] === true) {
    $retour['message'] = 'Connexion réussie mais aucun résultat trouvé pour cette requête.';
    $retour['code'] = lib\CodeDeRetourApi::NoContent->value;
}
a:
if (empty($reponse['code'])) {
    $reponse['code'] = lib\CodeDeRetourApi::OK->value;
}
middleware\Sanitizer::PostSanitize($retour);
http_response_code($reponse['code']);
echo json_encode($retour, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
exit;
