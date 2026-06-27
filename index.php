<?php

namespace Koyok\democratia;

use Exception;
use Koyok\democratia\data\query\Api;
use Koyok\democratia\lib\CodeDeRetourApi;
use Koyok\democratia\lib\DeleteMethode;
use Koyok\democratia\lib\GetMethode;
use Koyok\democratia\lib\ImageManager;
use Koyok\democratia\lib\PatchMethode;
use Koyok\democratia\lib\PostMethode;
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

try {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    if ($requestMethod === 'POST' || $requestMethod == 'PATCH') {
        $jsonRaw = file_get_contents('php://input');
        $data = json_decode($jsonRaw, true);
    }
    $config = new ServeurConfiguration;
    [$uri,$client, $isInDeveloppment, $isInProduction] = $config->Configure();
    [$requete, $parameters, $error] = Sanitizer::Sanitize();
    $jwtChecker = $config->JWTConfiguration($requete, $requestMethod, $parameters);
    if ($jwtChecker == null) {
        goto fin;
    }
    [$router, $request] = Router::GetInstace();
    Router::SetMiddleware();
    Router::Register();
    $response = $router->dispatch($request);
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
            throw new Exception("Méthode non prise en compte par l'api", CodeDeRetourApi::BadRequest->value);
    }
    if (! empty($error)) {
        throw new Exception($error['message'], $error['code']);
    }
    $config->BucketChecking($jwtChecker);
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
fin:
OutputFormat::OutputFormating($retour);
exit;
