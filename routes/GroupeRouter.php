<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\domain\controllers\GroupeController;
use League\Route\RouteGroup;

final class GroupeRouter implements RouterInterface
{
    public static function Register(): void
    {
        [$router, $_] = Router::GetInstace();
        Router::SetContainer(GroupeController::class);
        $router->group('/groupes', function (RouteGroup $route) {
            $route->post('/theme', [GroupeController::class, 'AjouterTheme']);
            $route->post('/internaute', [GroupeController::class, 'AjouterInternaute']);
            $route->get('/{idInternaute:uuid}', [GroupeController::class, 'GetGroupe']);
            $route->get('/obtenirImageGroupes/{idInternaute:uuid}', [GroupeController::class, 'GetImageDeGroupe']);
            $route->get('/{idGroupe:uuid}/thematiqueJoin', [GroupeController::class, 'GetThematiquesDUnGroupe']);
            $route->post('', [GroupeController::class, 'AjouterGroupe']);
            $route->get('/publierImage/{idGroupe}', [GroupeController::class, 'PublierImageGroupe']);
        });
    }
}
