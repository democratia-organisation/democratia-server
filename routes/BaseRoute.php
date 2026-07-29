<?php

namespace Koyok\democratia\routes;

final class BaseRoute implements RouterInterface
{
    public static function Register(): void
    {
        [$router, $_] = Router::GetInstace();
        $router->get('/dashboard', []);
    }
}
