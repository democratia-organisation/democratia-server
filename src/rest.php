<?php

namespace Koyok\democratia\src;

use Exception;
use Jose\Component\Checker;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature;
use Koyok\democratia\Extension;
use Symfony\Component\Dotenv\Dotenv;
use Throwable;

require_once '../vendor/autoload.php';

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
    http_response_code(CodeDeRetourApi::BadRequest->value);
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
    $algorithmManager = new AlgorithmManager([new Signature\Algorithm\ES256]);
    $jwsBuilder = new Signature\JWSBuilder($algorithmManager);
    $jwtSerializer = new Signature\Serializer\CompactSerializer;
    $clock = new Extension\ClockImplementation;
    $arrayChecker = [
        new Checker\ExpirationTimeChecker(clock: $clock),
        new Checker\IssuerChecker([$uri]),
        new Checker\AudienceChecker($client),
    ];
    $keyFile = dirname(__DIR__, 1).'/config/private.key';
    if (file_exists($keyFile)) {
        $privateKey = JWKFactory::createFromValues(json_decode(file_get_contents($keyFile), true));
    } else {
        $privateKey = JWKFactory::createECKey('P-256', ['alg' => 'ES256', 'use' => 'sig']);
        file_put_contents($keyFile, json_encode($privateKey->jsonSerialize()));
    }

    if (empty($header['Authorization']) && $requete != 'login') {
        throw new Exception('Entête incorrect', CodeDeRetourApi::Unauthorized->value);
    } elseif ($requete == 'login' && $requestMethod == 'GET') {

        $payloadAcces = json_encode([
            'iss' => $uri,
            'aud' => $client,
            'sub' => $parameters[0],
            'iat' => time(),
            'exp' => time() + 3600,
        ]);
        $payloadRefresh = json_encode([
            'iss' => $uri,
            'aud' => $client,
            'sub' => $parameters[0],
            'iat' => time(),
            'exp' => time() + 3600 * 24 * 7,
        ]);

        $jws = $jwsBuilder
            ->create()
            ->withPayload($payloadAcces)
            ->addSignature($privateKey, ['alg' => 'ES256'])
            ->build();
        $jwsRefresh = $jwsBuilder
            ->create()
            ->withPayload($payloadRefresh)
            ->addSignature($privateKey, ['alg' => 'ES256'])
            ->build();

        $tokenAccess = $jwtSerializer->serialize($jws);
        $tokenRefresh = $jwtSerializer->serialize($jwsRefresh);
        http_response_code(CodeDeRetourApi::OK->value);
        echo json_encode(['data' => ['API_KEY' => $tokenAccess, 'REFRESH' => $tokenRefresh]]);
        exit;
    } elseif (($requete == 'relogin' || $requete == 'SELECT * FROM internaute WHERE courriel=?') && $requestMethod == 'GET') {
        $arrayChecker[3] = new Extension\SubjectChecker($parameters[0]);

    }
    $claimChecker = new Checker\ClaimCheckerManager($arrayChecker);
    $jwsVerifier = new Signature\JWSVerifier($algorithmManager);
    $headerCheckerManager = new Checker\HeaderCheckerManager([new Checker\AlgorithmChecker(['ES256'])], [new Signature\JWSTokenSupport]);
    $token = str_replace('Bearer ', '', $header['Authorization'] ?? '');
    $jws = $jwtSerializer->unserialize($token);
    $payload = json_decode($jws->getPayload(), true);
    try {
        if (! $jwsVerifier->verifyWithKey($jws, $privateKey, 0)) {
            throw new Exception;
        }
        $claimValide = $claimChecker->check($payload);
        $headerCheckerManager->check($jws, 0);
    } catch (Checker\InvalidClaimException $th) {
        if ($th->getClaim() == 'exp') {
            throw new Exception('Token expiré', CodeDeRetourApi::Conflict->value);
        }
        if ($th->getClaim() == 'sub') {
            throw new Exception('Utilisateur incorérent', CodeDeRetourApi::Unauthorized->value);
        }

        throw new Exception('Token invalide', CodeDeRetourApi::Malicious->value);
    }
    $account = $payload['sub'];
    $bucket = Bucket::deserialiser($account);
    if (Bucket::hasABucket($account)) {
        $nombreBille = Bucket::getRatio($account);
        if ($nombreBille >= Bucket::$MAXIMUM_BILLES_USER) {
            throw new Exception("Le nombre de requete par l'utilisateur a été atteint", CodeDeRetourApi::UnprocessableEntity->value);
        }
        $bucket->addRequest();
    } elseif (! Bucket::serialiser($account)) {
        throw new Exception('Error Processing Request', CodeDeRetourApi::InternalServerError->value);
    }

    $api = new Api;
    $api->verificationValeurDonne($requete);
    switch ($requestMethod) {
        case 'GET':
            $test = '/SELECT/i';
            $requeteFinal = $api->tryGetAction($requete, GetMethode::class);
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
                GetGroupeImage($parameters[0]);
            } elseif ($requete == 'relogin') {
                $api->reponseApi();
                http_response_code($api->getCode());
                $api->get($parameters, 'SELECT * FROM internaute WHERE courriel=?');
                $api->reponseApi();
                $resultatFinal = [
                    'success' => $api->isSuccess,
                    'message' => $api->getMessage(),
                    'data' => $api->getTabRetour(),
                ];
                echo json_encode($resultatFinal, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                exit;
            } elseif ($requeteFinal == null) {
                $api->verificationFormatage($parameters, $requete);
                $api->verificationBonneAction($requete, $test);
                $requeteFinal = $requete;
            }
            $api->get($parameters, $requeteFinal);
            break;
        case 'POST':
            $test = '/INSERT/i';
            $requeteFinal = $api->tryGetAction($requete, PostMethode::class);
            if ($requeteFinal == null) {
                $api->verificationFormatage($parameters, $requete);
                $api->verificationBonneAction($requete, $test);
                $requeteFinal = $requete;
            } elseif ($requete == 'publierImage') {
                UploadGroupeImage($parameters[0]);
            }
            $api->post($parameters, $requeteFinal);
            break;
        case 'PATCH':
            $test = '/UPDATE/i';
            $requeteFinal = $api->tryGetAction($requete, PatchMethode::class);
            if ($requeteFinal == null) {
                $api->verificationFormatage($parameters, $requete);
                $api->verificationBonneAction($requete, $test);
                $requeteFinal = $requete;
            }
            $api->patch($parameters, $requeteFinal);
            break;
        case 'DELETE':
            $test = '/DELETE/i';
            $requeteFinal = $api->tryGetAction($requete, DeleteMethode::class);
            if ($requeteFinal == null) {
                $api->verificationFormatage($parameters, $requete);
                $api->verificationBonneAction($requete, $test);
                $requeteFinal = $requete;
            }
            $api->delete($parameters, $requeteFinal);
            break;
        default:
            throw new Exception('Aucune methode precise', CodeDeRetourApi::BadRequest->value);
    }
    $retour = $api->getTabRetour();
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
    if ($e->getCode() == CodeDeRetourApi::Malicious->value) {
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

$api->reponseApi();
http_response_code($api->getCode());
$resultatFinal = $api->getTabRetour();
echo json_encode($resultatFinal, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
exit;
