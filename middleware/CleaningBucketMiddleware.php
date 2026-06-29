<?php

namespace Koyok\democratia\middleware;

use EvTimer;
use Exception;
use Koyok\democratia\lib\CodeDeRetourApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CleaningBucketMiddleware implements MiddlewareInterface
{
    private Bucket $bucket;

    private static int $tempNettoyage = 3600 * 60;

    private static int $tempsVerifUsage = 60 * 5;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $w1 = new EvTimer(CleaningBucketMiddleware::$tempNettoyage, 0, function () {
            $verification = $this->bucket;
            if (! $verification) {
                throw new Exception('Erreur inattendu', CodeDeRetourApi::InternalServerError->value);
            }
        });
        $w2 = new EvTimer(CleaningBucketMiddleware::$tempsVerifUsage, 0, function () {
            $usage = Bucket::getGlobalUsage();
            if ($usage >= Bucket::$MAXIMUM_BILLES_GLOBAL) {
                throw new Exception('Le nombre de requete maximal a été atteint', CodeDeRetourApi::InternalServerError->value);
            }
        });

        return $handler->handle($request);

    }

    public function __construct(Bucket $bucket)
    {
        $this->bucket = $bucket;
    }
}
