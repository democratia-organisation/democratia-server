<?php

namespace Koyok\democratia\domain\utils;

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
                exit(json_encode(['success' => false, 'message' => 'Fichier trop grop', 'status' => CodeDeRetourApi::NoContent->value]));
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (! in_array($extension, $allowedExtensions)) {
                http_response_code(CodeDeRetourApi::BadRequest->value);
                exit(json_encode(['success' => false, 'message' => 'Format non autorisé', 'status' => CodeDeRetourApi::BadRequest->value]));
            }

            $check = getimagesize($file['tmp_name']);
            if ($check === false) {
                http_response_code(CodeDeRetourApi::Malicious->value);
                exit(json_encode(['success' => false, 'message' => "Le fichier n'est pas une image réelle", 'status' => CodeDeRetourApi::Malicious->value]));
            }

            $newName = uniqid('img_', true).'.'.$extension;
            $targetPath = "$targetDir$newName";

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $api->execute([$newName, $id], 'UPDATE groupe SET image=? WHERE id_groupe=UUID_TO_BIN(?, 0)');
                http_response_code(CodeDeRetourApi::OK->value);
                echo json_encode(['success' => true, 'data' => [], 'status' => CodeDeRetourApi::OK->value]);
                exit;
            } else {
                http_response_code(CodeDeRetourApi::InternalServerError->value);
                echo json_encode(['success' => false, 'message' => 'Erreur lors du transfert', 'status' => CodeDeRetourApi::InternalServerError->value]);
                exit;
            }
        }
    }

    public static function GetGroupeImage(string $nom_image): void
    {
        try {
            $baseDir = dirname(__DIR__, 1).'/images/';
            $fileName = file_exists($baseDir.$nom_image) ? $baseDir.$nom_image : $baseDir.'default-groupe.png.jpeg';
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
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
