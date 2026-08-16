<?php

namespace Koyok\democratia\routes;

use HaydenPierce\ClassFinder\ClassFinder;
use Koyok\democratia\data\query\Api;
use Koyok\democratia\middleware\{ErrorFormatMiddleware, JWTMiddleware, OutputFormatMiddleware, ServeurConfiguration};
use Laminas\Diactoros\{ResponseFactory, ServerRequestFactory};
use League\Container\Container;
use League\Route\Strategy\JsonStrategy;
use Psr\Http\Message\{RequestInterface, ServerRequestInterface};

final class Router
{
    private static ?\League\Route\Router $router = null;

    private static ?ServerRequestInterface $request = null;

    private static ?Container $container = null;

    private static ?ResponseFactory $responseFactory = null;

    private static ?JsonStrategy $strategy = null;

    private static array $personalizeRoutes = [];

    public static string $JWT_ATTRIBUTE = 'JWT_KEY';

    /**
     * @return array{router: \League\Route\Router, request: RequestInterface}
     */
    public static function GetInstace(): array
    {
        if (Router::$router == null && Router::$request == null) {
            Router::$request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
            Router::$router = new \League\Route\Router;
        }

        return [Router::$router, Router::$request];
    }

    public static function SetRoute(): void
    {
        $classes = ClassFinder::getClassesInNamespace('Koyok\\democratia\\routes');
        Router::$personalizeRoutes = array_filter($classes, fn ($class) => is_subclass_of($class, RouterInterface::class));
    }

    public static function SetMiddleware(): void
    {
        if (Router::$router != null) {
            Router::$router->middlewares([new ErrorFormatMiddleware, new JWTMiddleware, new OutputFormatMiddleware]);
            [$isInDev, $isInProd] = ServeurConfiguration::EnvDetermination();
            if ($isInProd) {
                Router::$router->setScheme('https');
                Router::$router->setPort(443);
            } elseif ($isInDev) {
                Router::$router->setScheme('http');
                Router::$router->setPort(80);
            }
            Router::$router->setHost(getenv('URL'));
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
        $methode = 'Register';
        foreach (Router::$personalizeRoutes as $classe) {
            $classe::$methode();
        }
    }
}
