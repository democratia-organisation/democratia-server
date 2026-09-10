<?php

namespace Koyok\democratia\middleware;

use Koyok\democratia\lib\{KafkaProducer, KafkaStatisticMessage};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

final class PerformanceMeasureMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $debut = microtime(true);
        $response = $handler->handle($request);
        $fin = microtime(true);
        $duree = $fin - $debut;
        $producer = new KafkaProducer;
        $options = new KafkaStatisticMessage($duree, $request->getUri()->getPath(), $request->getMethod(), $response->getStatusCode());
        $producer->Produce($options);

        return $response;
    }
}
