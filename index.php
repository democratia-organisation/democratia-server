<?php

namespace Koyok\democratia;

use Koyok\democratia\middleware\{ErrorFormatMiddleware, ServeurConfigurationMiddleware};
use Koyok\democratia\routes\Router;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

require_once './vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

[$router, $request] = Router::GetInstace();
Router::SetMiddleware();
Router::SetRoute();
Router::Register();

$emitter = new SapiEmitter;
try {
    $response = $router->dispatch($request);
    $emitter->emit($response);
} catch (\Throwable $th) {
    [$isInDeveloppment, $isInProduction] = ServeurConfigurationMiddleware::EnvDetermination();
    [$response, $code] = new ErrorFormatMiddleware()->ErrorFormating($th, $isInProduction, $isInDeveloppment);
    $emitter->emit(new JsonResponse($response, status: $code));
}
