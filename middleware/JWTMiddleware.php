<?php

namespace Koyok\democratia\middleware;

use Exception;
use Koyok\democratia\domain\Extension\SubjectChecker;
use Koyok\democratia\lib\CodeDeRetourApi;
use Koyok\democratia\routes\Router;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

final class JWTMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $value = $this->JWTConfiguration($request);
        if (\is_array($value)) {
            return new JsonResponse($value, CodeDeRetourApi::OK->value);
        } elseif (\is_string($value)) {
            $request = $request->withAttribute(Router::$JWT_ATTRIBUTE, $value);

            return $handler->handle($request);
        } else {
            throw new Exception('Error Processing Request', CodeDeRetourApi::InternalServerError->value);
        }
    }

    private function JWTConfiguration(ServerRequestInterface $request): string|array
    {
        $header = getallheaders();
        $uri = $request->getUri()->getHost();
        $client = $request->getServerParams()['REMOTE_ADDR'];
        $requestMethod = $request->getMethod();
        $body = [];
        $jwtChecker = new JwtChecker($uri, $client);
        [$isInDeveloppment, $isInProduction] = ServeurConfigurationMiddleware::EnvDetermination();
        $path = $request->getUri()->getPath();
        if (empty($header['Authorization'])) {
            if ($path == '/dashboard') {
                if ($isInDeveloppment || $isInProduction) {
                    ServeurConfigurationMiddleware::Dashboard($isInProduction);

                    return null;
                } else {
                    throw new Exception('Aucun acces', CodeDeRetourApi::Malicious->value);
                }
            } else {
                throw new Exception('Entête incorrect', CodeDeRetourApi::Unauthorized->value);
            }
        } else {
            $stream = $request->getBody();
            $content = $stream->getContents();
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
            $body = json_decode($content, true);
            if ($path == '/users/login' && $requestMethod == 'POST') {
                $jwtChecker->arrayChecker[3] = new SubjectChecker($body[0]);
                $jwtChecker->CheckJWT($header);

                return $jwtChecker->GetPayload()['sub'];
            } elseif ($path == '/users/refresh' && $requestMethod == 'POST') {
                return $jwtChecker->GenerateKey($body[0]);
            } else {
                throw new Exception('Error Processing Request', 1);
            }
        }
    }
}
