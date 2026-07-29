<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\domain\controllers\ThematiqueController;
use League\Route\RouteGroup;

final class ThematiqueRouter implements RouterInterface
{
    public static function Register(): void
    {
        [$router, $_] = Router::GetInstace();
        Router::SetContainer(ThematiqueController::class);
        $router->group('/thematiques', function (RouteGroup $route) {
            $route->get('', [ThematiqueController::class, 'GetAllGroupe']);
            $route->post('', [ThematiqueController::class, 'GetAllGroupe']);
        });
    }
}
