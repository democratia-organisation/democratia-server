<?php

namespace Koyok\democratia\middleware;

use Koyok\democratia\domain\utils;

final class Sanitizer
{
    public static function Sanitize(): array
    {
        $requete = '';
        $parameters = [];
        $error = [];

        $requeteRaw = $_GET['request'] ?? '';
        if ($requeteRaw === '') {
            if ($_SERVER['REQUEST_URI'] == '/dashboard') {
                $requete = 'dashboard';
            } else {
                $error = ['success' => false, 'message' => 'no parameters', 'code' => utils\CodeDeRetourApi::BadRequest->value];
            }
        } else {
            while (strpos($requeteRaw, '%') !== false) {
                $requeteRaw = urldecode($requeteRaw);
            }
            $requete = trim($requeteRaw);
            $requete = preg_replace('/\s+$/', '', $requeteRaw);

            $requete = $requeteRaw;
            $parameters = [];
            if (isset($_GET['parameters'])) {
                $paramsRaw = $_GET['parameters'];
                if (strpos($paramsRaw, '%25') !== false) {
                    $paramsRaw = urldecode($paramsRaw);
                }
                $decodedJson = json_decode(urldecode($paramsRaw), true);
                $parameters = \is_array($decodedJson) ? array_values($decodedJson) : [];
            }
        }

        return [$requete, $parameters, $error];

    }

    public static function PostSanitize(array $tableauReponse): void
    {
        array_walk_recursive($tableauReponse, function (&$item) {
            if (\is_string($item)) {
                $item = preg_replace('/[\x00-\x1F\x7F]/u', '', $item);
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });
    }
}
