<?php

namespace Koyok\democratia\middleware;

use Koyok\democratia\lib\CodeDeRetourApi;
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
            $response = $response->withBody($stream);
            $response->getBody()->write(json_encode($retour));
        }

        return $response;
    }

    public function OutputFormating(array $retour): array
    {
        if (empty($retour['data']) && $retour['success'] === true) {
            $retour['message'] = 'Connexion réussie mais aucun résultat trouvé pour cette requête.';
            $retour['code'] = CodeDeRetourApi::NoContent->value;
        }
        if (empty($reponse['code'])) {
            $reponse['code'] = CodeDeRetourApi::OK->value;
        }
        if (\count($retour) != 0) {
            http_response_code($reponse['code']);
        }

        return $retour;

    }
}
