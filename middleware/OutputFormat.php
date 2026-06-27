<?php

namespace Koyok\democratia\middleware;

use Koyok\democratia\lib\CodeDeRetourApi;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class OutputFormat implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new RedirectResponse($request->getUri());
    }

    public static function ErrorFormating(Throwable $e, bool $isInProduction, bool $isInDeveloppment): void
    {
        $errorCode = $e->getCode();
        $code = $errorCode >= 200 & $errorCode < 400 ? CodeDeRetourApi::InternalServerError->value : $errorCode;
        http_response_code($code);
        $reponse = [
            'success' => false,
            'message' => 'Une erreur inattendu est survenu',
        ];
        if ($e->getCode() == CodeDeRetourApi::Malicious->value && $isInProduction) {
            header('Location: https://www.youtube.com/watch?v=dQw4w9WgXcQ');
            exit;
        }
        if ($isInDeveloppment) {
            $reponse['file'] = $e->getFile();
            $reponse['line'] = $e->getLine();
            $reponse['error_type'] = get_class($e);
            $reponse['message'] = $e->getMessage();
            $reponse['stackTrace'] = $e->getTraceAsString();
        }
        echo json_encode($reponse, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    public static function OutputFormating(array $retour): void
    {
        if (empty($retour['data']) && $retour['success'] === true) {
            $retour['message'] = 'Connexion réussie mais aucun résultat trouvé pour cette requête.';
            $retour['code'] = CodeDeRetourApi::NoContent->value;
        }
        if (empty($reponse['code'])) {
            $reponse['code'] = CodeDeRetourApi::OK->value;
        }
        Sanitizer::PostSanitize($retour);
        http_response_code($reponse['code']);
        echo json_encode($retour, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    }
}
