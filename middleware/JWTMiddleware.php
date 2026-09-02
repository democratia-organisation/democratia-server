<?php

namespace Koyok\democratia\middleware;

use Exception;
use Koyok\democratia\lib\CodeDeRetourApi;
use Koyok\democratia\routes\Router;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

final class JWTMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $config = new ServeurConfiguration;
        $config->Configure();
        $param = null;
        $path = $request->getUri()->getPath();
        $isNotif = str_contains($path, '/notifications');
        if ($path == '/users/refresh' || $path == '/users/login') {
            $stream = $request->getBody();
            $content = $stream->getContents();
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
            $param = json_decode($content, true);
        }
        $value = $config->JWTConfiguration($request->getUri()->getPath(), $request->getMethod(), $param);
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
