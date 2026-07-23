<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\domain\controllers\InternauteController;
use League\Route\RouteGroup;

final class InternauteRouter implements RouterInterface
{
    public static function Register(): void
    {
        [$router, $_] = Router::GetInstace();
        Router::SetContainer(InternauteController::class);
        $router->group('/users', function (RouteGroup $route) {
            $route->get('/groupes/{idInternaute:number}', [InternauteController::class, 'GetGroupe']);
            $route->post('/login', [InternauteController::class, 'Login']);
            $route->post('/refresh', []);
            $route->patch('', [InternauteController::class, 'ModifierInternaute']);
            $route->post('', [InternauteController::class, 'CreerInternaute']);
            $route->delete('/{idInternaute:number}', [InternauteController::class, 'SupprimerInternaute']);
            $route->get('/{email:word}/doublon', [InternauteController::class, 'GetMailDoublon']);
            $route->get('/{idInternaute:number}', [InternauteController::class, 'GetInternaute']);

        });
    }
}
