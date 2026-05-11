<?php

namespace Koyok\democratia\lib;

use Exception;
use Koyok\democratia\data\query\Api;

final class ImageManager
{
    public static function UploadGroupeImage(string $id): void
    {
        $api = new Api;
        $targetDir = __DIR__.'/images/';
        $maxFileSize = 10 * 1024 * 1024;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
            $file = $_FILES['image'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($_FILES['image']['size'] > $maxFileSize) {
                http_response_code(CodeDeRetourApi::NoContent->value);
                exit;
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (! \in_array($extension, $allowedExtensions)) {

                http_response_code(CodeDeRetourApi::BadRequest->value);
                exit;
            }

            $check = getimagesize($file['tmp_name']);
            if ($check === false) {

                http_response_code(CodeDeRetourApi::Malicious->value);
                exit;
            }

            $newName = uniqid('img_', true).'.'.$extension;
            $targetPath = "$targetDir$newName";

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $api->execute([$newName, $id], 'UPDATE groupe SET image=? WHERE id_groupe=UUID_TO_BIN(?, 0)');

                http_response_code(CodeDeRetourApi::NoContent->value);
                exit;
            } else {

                http_response_code(CodeDeRetourApi::InternalServerError->value);
                exit;

            }
        } else {
            http_response_code(CodeDeRetourApi::BadRequest->value);
            exit;
        }
    }

    public static function GetGroupeImage(string $nom_image): void
    {
        try {
            $baseDir = dirname(__DIR__, 1).'/images';
            $fileName = file_exists("$baseDir/$nom_image") ? "$baseDir/$nom_image" : "$baseDir/defaultgroupe.png.jpeg";
            $filePath = $fileName;
            $mimeType = mime_content_type($filePath);

            if (ob_get_level()) {
                ob_end_clean();
            }

            header("Content-Type: $mimeType");
            header('Content-Length: '.filesize($filePath));

            readfile($filePath);

            exit;

        } catch (Exception $e) {

            http_response_code(CodeDeRetourApi::InternalServerError->value);
            exit;
        }
    }
}
