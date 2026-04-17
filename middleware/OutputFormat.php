<?php

namespace Koyok\democratia\middleware;

use Koyok\democratia\lib\CodeDeRetourApi;
use Throwable;

final class OutputFormat
{
    public static function ErrorFormating(Throwable $e, bool $isInProduction, bool $isInDeveloppment): void
    {
        http_response_code($e->getCode());
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
            $reponse['error_type'] = $e->getMessage();
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
