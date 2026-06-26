<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\domain\controllers\InternauteController;
use League\Route\RouteGroup;

final class InternauteRouter
{
    public static function Register(): void
    {
        [$router , $_] = Router::GetInstace();
        Router::SetContainer(InternauteController::class);
        $router->group('/users', function (RouteGroup $route) {
            $route->get('/groupes/{idInternaute:number}', [InternauteController::class, 'GetGroupe']);
            $route->post('/login', [InternauteController::class, 'GetGroupe']);
            $route->delete('/{idInternaute:number}', [InternauteController::class, 'GetGroupe']);
            $route->patch('', [InternauteController::class, 'GetGroupe']);
            $route->post('', [InternauteController::class, 'GetGroupe']);
            $route->get('/{email}/doublon', [InternauteController::class, 'GetGroupe']);
        });
    }
}
