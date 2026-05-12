<?php

namespace Koyok\democratia;

use DateTime;
use Exception;
use Koyok\democratia\data\query\Api;
use Koyok\democratia\domain\Extension;
use Koyok\democratia\lib\DeleteMethode;
use Koyok\democratia\lib\GetMethode;
use Koyok\democratia\lib\ImageManager;
use Koyok\democratia\lib\PatchMethode;
use Koyok\democratia\lib\PostMethode;
use Koyok\democratia\middleware\Bucket;
use Koyok\democratia\middleware\JwtChecker;
use Koyok\democratia\middleware\OutputFormat;
use Koyok\democratia\middleware\RequestVerificator;
use Koyok\democratia\middleware\Sanitizer;
use Koyok\democratia\middleware\ServeurConfiguration;
use Koyok\democratia\routes\Router;
use Throwable;

require_once './vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['QUERY_STRING'];
[$uri,$client, $isInDeveloppment, $isInProduction] = ServeurConfiguration::Configure();
$router = new Router;
$router->Routing($path, $requestMethod);
$_GET['request'] = $router->request;
$_GET['parameters'] = $router->parameters;
[$requete, $parameters, $error] = Sanitizer::Sanitize();

$test = '';
$methodeToCheck = '';
$api = new Api;

switch ($requestMethod) {
    case 'GET':
        $test = '/SELECT/i';
        $methodeToCheck = GetMethode::class;
        break;
    case 'POST':
        $test = '/INSERT/i';
        $methodeToCheck = PostMethode::class;
        break;
    case 'PATCH':
        $test = '/UPDATE/i';
        $methodeToCheck = PatchMethode::class;
        break;
    case 'DELETE':
        $test = '/DELETE/i';
        $methodeToCheck = DeleteMethode::class;
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
                ServeurConfiguration::Dashboard($isInDeveloppment, $isInProduction);
            } else {
                throw new Exception('Aucun acces', lib\CodeDeRetourApi::Malicious->value);
            }
        } else {
            throw new Exception('Entête incorrect', lib\CodeDeRetourApi::Unauthorized->value);
        }
    }

    $jwtChecker = new JwtChecker($uri, $client);
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

    $bucket = Bucket::deserialiser($account);
    if (Bucket::hasABucket($account)) {
        $nombreBille = Bucket::getRatio($account);
        if ($nombreBille >= Bucket::$MAXIMUM_BILLES_USER) {
            header('X-RateLimit-Reset: '.new DateTime()->getTimestamp() + Bucket::$tempNettoyage);
            header('Retry-After: 60');
            throw new Exception("Le nombre de requete par l'utilisateur a été atteint", lib\CodeDeRetourApi::RateLimit->value);
        } else {
            $bucket->addRequest();
            header('X-RateLimit-Limit: '.Bucket::$MAXIMUM_BILLES_USER);
            header('X-RateLimit-Remaining: '.Bucket::$MAXIMUM_BILLES_USER - $bucket->nombreBilles);
        }
    } elseif (! Bucket::serialiser($account)) {
        throw new Exception('Error Processing Request', lib\CodeDeRetourApi::InternalServerError->value);
    }

    RequestVerificator::verificationValeurDonne($requete);
    switch ($requete) {
        // pas de break car les deux fonction exit le programme d'elles mêmes
        case 'obtenirImage':
            ImageManager::GetGroupeImage($parameters[0]);
        case 'publierImage':
            ImageManager::UploadGroupeImage($parameters[0]);
        default:
            RequestVerificator::verificationFormatage($parameters, $requete);
            RequestVerificator::verificationBonneAction($requete, $test);
            $potentielAction = $api->tryGetAction($requete, $methodeToCheck);
            $requete = $potentielAction ?? $requete;
            $retour = $api->execute($parameters, $requete);
            break;
    }

} catch (Throwable $e) {
    OutputFormat::ErrorFormating($e, $isInProduction, $isInDeveloppment);
    exit;
}
a:
OutputFormat::OutputFormating($retour);
exit;
