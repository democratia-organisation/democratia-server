<?php

namespace Koyok\democratia\middleware;

use Koyok\democratia\domain\utils;

final class Sanitizer
{
    public static function Sanitize(): array
    {

        $requeteRaw = $_GET['request'] ?? '';
        if ($requeteRaw === null) {
            http_response_code(utils\CodeDeRetourApi::BadRequest->value);
            echo json_encode(['success' => false, 'message' => 'no parameters']);
            exit;
        }
        $requete = $requeteRaw;

        while (strpos($requete, '%') !== false) {
            $requete = urldecode($requete);
        }
        $requete = trim($requete);
        $requete = preg_replace('/\s+$/', '', $requete);
        $parameters = [];
        if (isset($_GET['parameters'])) {
            $paramsRaw = $_GET['parameters'];

            if (strpos($paramsRaw, '%25') !== false) {
                $paramsRaw = urldecode($paramsRaw);
            }
            $decodedJson = json_decode(urldecode($_GET['parameters']), true);
            $parameters = is_array($decodedJson) ? array_values($decodedJson) : [];
        }

        return [$requete, $parameters];

    }

    public static function PostSanitize(array $tableauReponse): void
    {
        array_walk_recursive($tableauReponse, function (&$item) {
            if (is_string($item)) {
                $item = preg_replace('/[\x00-\x1F\x7F]/u', '', $item);
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });
    }
}
