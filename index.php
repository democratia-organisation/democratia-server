<?php

namespace Koyok\democratia;

use Koyok\democratia\middleware\{ErrorFormatMiddleware, ServeurConfiguration};
use Koyok\democratia\routes\Router;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

require_once './vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

[$router, $request] = Router::GetInstace();
Router::SetMiddleware();
Router::SetRoute();
Router::Register();
try {
    $response = $router->dispatch($request);
    (new SapiEmitter)->emit($response);
} catch (\Throwable $th) {
    [$isInDeveloppment, $isInProduction] = ServeurConfiguration::EnvDetermination();
    $response = new ErrorFormatMiddleware()->ErrorFormating($th, $isInProduction, $isInDeveloppment);
    echo json_encode($response);
}
