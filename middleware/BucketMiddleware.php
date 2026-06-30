<?php

namespace Koyok\democratia\middleware;

use Koyok\democratia\routes\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class BucketMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $account = $request->getAttribute(Router::$JWT_ATTRIBUTE);
        $config = new ServeurConfiguration;
        $config->BucketChecking($account);

        return $handler->handle($request);

    }
}
