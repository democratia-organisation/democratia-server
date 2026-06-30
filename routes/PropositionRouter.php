<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\domain\controllers\PropositionController;
use League\Route\RouteGroup;

final class PropositionRouter
{
    public static function Register(): void
    {
        [$router,$_] = Router::GetInstace();
        Router::SetContainer(PropositionController::class);
        $router->group('/propositions', function (RouteGroup $route) {
            $route->get('/{idGroupe:uuid}', [PropositionController::class, 'GetPropostionsDUnGroupe']);

        });
    }
}
