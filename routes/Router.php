<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\data\query\Api;
use Koyok\democratia\middleware;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use League\Container\Container;
use League\Route\Strategy\JsonStrategy;
use Psr\Http\Message\ServerRequestInterface;

final class Router
{
    private static ?\League\Route\Router $router = null;

    private static ?ServerRequestInterface $request = null;

    private static ?Container $container = null;

    private static ?ResponseFactory $responseFactory = null;

    private static ?JsonStrategy $strategy = null;

    public static function GetInstace(): array
    {
        if (Router::$router == null && Router::$request == null) {
            Router::$request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
            Router::$router = new \League\Route\Router;
        }

        return [Router::$router, Router::$request];
    }

    public static function SetMiddleware(): void
    {
        if (Router::$router != null) {
            Router::$router->middlewares([middleware\ErrorFormatMiddleware::class, middleware\JWTMiddleware::class, middleware\BucketMiddleware::class, middleware\CleaningBucketMiddleware::class, middleware\OutputFormatMiddleware::class]);
        }
    }

    public static function SetContainer(string $className): void
    {
        if (Router::$container == null) {
            Router::$container = new Container;
        }
        if (Router::$responseFactory == null) {
            Router::$responseFactory = new ResponseFactory;
        }
        if (Router::$strategy == null) {
            Router::$strategy = new JsonStrategy(Router::$responseFactory);
        }
        if (Router::$request != null) {
            Router::$container->add($className)->addArgument(Api::class);
            Router::$container->add(Api::class);

            Router::$strategy->setContainer(Router::$container);
            Router::$router->setStrategy(Router::$strategy);
        }

    }

    public static function Register(): void
    {
        InternauteRouter::Register();
        PropositionRouter::Register();
        ThematiqueRouter::Register();
        GroupeRouter::Register();
    }
}
