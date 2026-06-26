<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\data\query\Api;
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

    public static function GetInstace(): array
    {
        if (Router::$router == null && Router::$request == null) {
            Router::$request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
            Router::$router = new \League\Route\Router;
        }

        return [Router::$router, Router::$request];
    }

    public static function SetContainer(string $className): void
    {
        if (Router::$container == null) {
            Router::$container = new Container;

        }
        if (Router::$request != null) {
            Router::$container->add($className)->addArgument(Api::class);
            Router::$container->add(Api::class);
            $responseFactory = new ResponseFactory;
            $strategy = new JsonStrategy($responseFactory)->setContainer(Router::$container);
            Router::$router->setStrategy($strategy);
        }

    }

    public static function Register(): void
    {
        InternauteRouter::Register();
    }
}
