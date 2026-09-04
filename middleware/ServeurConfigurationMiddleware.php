<?php

namespace Koyok\democratia\middleware;

use Exception;
use Koyok\democratia\lib\CodeDeRetourApi;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

final class ServeurConfigurationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        $uri = $_SERVER['HTTP_HOST'];
        [$isInDeveloppment, $isInProduction] = ServeurConfigurationMiddleware::EnvDetermination();
        if ($isInDeveloppment) {
            $uri = 'http://'.$uri;
            ini_set('display_errors', 0);
            error_reporting(E_ALL);
        } elseif ($isInProduction) {
            $uri = 'https://'.$uri;
            ServeurConfigurationMiddleware::Https_Configuration();
            error_reporting(E_ERROR | E_PARSE);
        } else {
            throw new Exception('Paramètre de production invalide ou corrompu', CodeDeRetourApi::Malicious->value);
        }

        return $handler->handle($request);
    }

    private static function Https_Configuration(): void {}

    public static function Dashboard(bool $isInProd): void
    {
        ServeurConfigurationMiddleware::AuthentificationPageGeneration();
        if ($isInProd) {
            ServeurConfigurationMiddleware::TWOFAAuthentification();
        }
        $page = file_get_contents('/var/www/html/dashboard.html');
        echo $page;
        exit;

    }

    /**
     * Summary of EnvDetermination
     *
     * @return bool[]
     */
    public static function EnvDetermination(): array
    {
        $environement = ServeurConfigurationMiddleware::EnvScanning('ENVIRONNEMENT');

        return [$environement == 'developpment', $environement == 'production'];
    }

    private static function EnvScanning(string $nomEnv): string
    {
        return getenv($nomEnv);
    }

    private static function AuthentificationPageGeneration(): void {}

    private static function TWOFAAuthentification(): void {}
}
