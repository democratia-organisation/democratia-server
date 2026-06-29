<?php

namespace Koyok\democratia\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class BucketMiddlware implements MiddlewareInterface
{
    private Bucket $bucket;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $account = $request->getBody();
        $config = new ServeurConfiguration;
        $config->BucketChecking($account);

        return $handler->handle($request);

    }

    public function __construct(Bucket $bucket)
    {
        $this->bucket = $bucket;
    }
}
