<?php

namespace Koyok\democratia\middleware;

use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

final class OutputFormatMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $retour = json_decode($response->getBody(), true);
        if ($retour != null) {
            $retour = $this->OutputFormating($retour);
            $stream = new StreamFactory()->createStream('');
            $response = $response->withBody($stream)->withStatus($retour['code']);
            $response->getBody()->write(json_encode($retour));
        }

        return $response;
    }

    public function OutputFormating(array $retour): array
    {
        // TODO : formattage issue de Api à implémenter ici
        return $retour;

    }
}
