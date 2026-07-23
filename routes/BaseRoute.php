<?php

namespace Koyok\democratia\routes;

use League\Route\RouteGroup;

final class BaseRoute implements RouterInterface
{
    public static function Register(): void
    {
        [$router, $_] = Router::GetInstace();
        $router->group('/', function (RouteGroup $route) {
            $route->get('/dashboard', []);
        });
    }
}
