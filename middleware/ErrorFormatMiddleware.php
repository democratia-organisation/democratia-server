<?php

namespace Koyok\democratia\middleware;

use Koyok\democratia\lib\CodeDeRetourApi;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Throwable;

final class ErrorFormatMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $th) {
            [$_, $_, $isInDeveloppment, $isInProduction] = new ServeurConfiguration()->Configure();
            [$retour, $code] = $this->ErrorFormating($th, $isInProduction, $isInDeveloppment);

            return new JsonResponse($retour, $code);
        }
    }

    public function ErrorFormating(Throwable $e, bool $isInProduction, bool $isInDeveloppment): array
    {
        $errorCode = $e->getCode();
        $code = $errorCode < 400 ? CodeDeRetourApi::InternalServerError->value : $errorCode;
        http_response_code($code);
        $reponse = [
            'sucess' => false,
            'message' => 'Une erreur inattendu est survenu',
        ];
        if ($code == CodeDeRetourApi::Malicious->value && $isInProduction) {
            header('Location: https://www.youtube.com/watch?v=dQw4w9WgXcQ');
            exit;
        }
        if ($isInDeveloppment) {
            $reponse['file'] = $e->getFile();
            $reponse['line'] = $e->getLine();
            $reponse['error_type'] = \get_class($e);
            $reponse['message'] = $e->getMessage();
            $reponse['stackTrace'] = $e->getTraceAsString();
        }

        return [$reponse, $code];
    }
}
