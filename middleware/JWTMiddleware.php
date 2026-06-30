<?php

namespace Koyok\democratia\middleware;

use Exception;
use Koyok\democratia\lib\CodeDeRetourApi;
use Koyok\democratia\routes\Router;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JWTMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $config = new ServeurConfiguration;
        $config->Configure();
        $value = $config->JWTConfiguration($request->getUri()->getPath(), $request->getMethod());
        if (\is_array($value)) {
            return new JsonResponse($value, CodeDeRetourApi::OK->value);
        } elseif (\is_string($value)) {
            $request = $request->withAttribute(Router::$JWT_ATTRIBUTE, $value);

            return $handler->handle($request);
        } else {
            throw new Exception('Error Processing Request', CodeDeRetourApi::InternalServerError->value);
        }

    }
}
