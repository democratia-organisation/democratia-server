<?php

namespace Koyok\democratia\lib;

use Exception;
use Koyok\democratia\data\query\Api;

final class ImageManager
{
    public static function UploadGroupeImage(string $id): array
    {
        $api = new Api;
        $targetDir = __DIR__.'/images/';
        $maxFileSize = 10 * 1024 * 1024;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
            $file = $_FILES['image'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($_FILES['image']['size'] > $maxFileSize) {
                return ['success' => false, 'message' => 'Fichier trop grop', 'code' => CodeDeRetourApi::NoContent->value];
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (! \in_array($extension, $allowedExtensions)) {

                return ['success' => false, 'message' => 'Format non autorisé', 'code' => CodeDeRetourApi::BadRequest->value];
            }

            $check = getimagesize($file['tmp_name']);
            if ($check === false) {

                return ['success' => false, 'message' => "Le fichier n'est pas une image réelle", 'code' => CodeDeRetourApi::Malicious->value];
            }

            $newName = uniqid('img_', true).'.'.$extension;
            $targetPath = "$targetDir$newName";

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $api->execute([$newName, $id], 'UPDATE groupe SET image=? WHERE id_groupe=UUID_TO_BIN(?, 0)');

                return ['success' => true, 'data' => [], 'code' => CodeDeRetourApi::NoContent->value];
            } else {

                return ['success' => false, 'message' => 'Erreur lors du transfert', 'code' => CodeDeRetourApi::InternalServerError->value];

            }
        } else {
            return ['success' => false, 'message' => 'Requete incorrect', 'code' => CodeDeRetourApi::BadRequest->value];
        }
    }

    public static function GetGroupeImage(string $nom_image): array
    {
        try {
            $baseDir = dirname(__DIR__, 3).'/images';
            $fileName = file_exists("$baseDir/$nom_image") ? "$baseDir/$nom_image" : "$baseDir/defaultgroupe.png.jpeg";
            $filePath = $fileName;
            $mimeType = mime_content_type($filePath);

            if (ob_get_level()) {
                ob_end_clean();
            }

            header("Content-Type: $mimeType");
            header('Content-Length: '.filesize($filePath));

            readfile($filePath);

            return ['success' => true, 'data' => [], 'code' => CodeDeRetourApi::OK->value];

        } catch (Exception $e) {

            return ['error' => $e->getMessage(), 'code' => CodeDeRetourApi::InternalServerError->value];
        }
    }
}
