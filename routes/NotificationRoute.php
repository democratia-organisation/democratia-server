<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\domain\controllers\NotificationController;
use League\Route\RouteGroup;

final class NotificationRoute implements RouterInterface
{
    public static function Register(): void
    {
        [$router, $_] = Router::GetInstace();
        Router::SetContainer(NotificationController::class);
        $router->group('/notifications', function (RouteGroup $route) {
            $route->patch('/{deviceId}', [NotificationController::class, 'RegitreNotification']);
            $route->delete('/{deviceId}', [NotificationController::class, 'DeleteNotification']);
            $route->patch('/choixUtilisateur/{idGroupe:uuid}/{idInternaute:uuid}', [NotificationController::class, 'EnregistrerChoix']);
        });
    }
}
