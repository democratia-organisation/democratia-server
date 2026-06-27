<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\domain\controllers\GroupeController;
use League\Route\RouteGroup;

final class GroupeRouter
{
    public static function Register(): void
    {
        [$router , $_] = Router::GetInstace();
        Router::SetContainer(GroupeController::class);
        $router->group('/groupes', function (RouteGroup $route) {
            $route->get('/{idGroupe:uuid}', [GroupeController::class, 'GetGroupe']);
            $route->get('/obtenirImageGroupe/{url}', [GroupeController::class, 'GetImageDeGroupe']);
            $route->get('/{idGroupe:uuid}/thematiqueJoin', [GroupeController::class, 'GetThematiquesDUnGroupe']);
            $route->post('', [GroupeController::class, 'AjouterGroupe']);
            $route->post('/theme', [GroupeController::class, 'AjouterTheme']);
            $route->post('/internaute', [GroupeController::class, 'AjouterInternaute']);
            $route->post('/{idGroupe:uuid}/publierImage', [GroupeController::class, 'PublierImageGroupe']);
        });
    }
}
