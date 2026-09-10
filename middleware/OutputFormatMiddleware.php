<?php

namespace Koyok\democratia\middleware;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

final class OutputFormatMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $response = $this->OutputFormating($response);

        return $response;
    }

    public function OutputFormating(ResponseInterface $response): ResponseInterface
    {
        $retour = json_decode($response->getBody(), true);
        $retour['sucess'] = $response->getStatusCode() < 399;
        $retour['message'] = \count($retour) > 0 ? 'Requête réussi' : 'Requêtre réussi sans contenu';
        $retour['data'] = $retour;
        $retour['code'] = $response->getStatusCode();

        return $response;

    }
}
