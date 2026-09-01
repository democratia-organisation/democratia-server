<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\domain\controllers\CommentaireController;
use League\Route\RouteGroup;

final class CommentaireRouter implements RouterInterface
{
    public static function Register(): void
    {
        [$router, $_] = Router::GetInstace();
        Router::SetContainer(CommentaireController::class);
        $router->group('/commentaires', function (RouteGroup $route) {
            $route->get('/{idGroupe:uuid}/{idProposition:number}', [CommentaireController::class, 'GetMessageFromProposition']);
            $route->post('/{idGroupe:uuid}/{idProposition:number}', [CommentaireController::class, 'PostMessage']);
        });
    }
}
