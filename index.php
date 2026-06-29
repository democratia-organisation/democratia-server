<?php

namespace Koyok\democratia;

use Koyok\democratia\routes\Router;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

require_once './vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'];
if ($requestMethod === 'POST' || $requestMethod == 'PATCH') {
    $jsonRaw = file_get_contents('php://input');
    $_POST = json_decode($jsonRaw, true);
}
[$router, $request] = Router::GetInstace();
Router::SetMiddleware();
Router::Register();
$response = $router->dispatch($request);
(new SapiEmitter)->emit($response);
