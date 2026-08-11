<?php

namespace Koyok\democratia\lib;

use Exception;
use GuzzleHttp\Psr7\Stream;
use Koyok\democratia\data\query\Api;

final class ImageManager
{
    public static function UploadImage(string $id, string $tableName, ?string $persnaliseRequete = null): int
    {
        $api = new Api;
        $targetDir = __DIR__.'/images/';
        $maxFileSize = 10 * 1024 * 1024;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
            $file = $_FILES['image'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($_FILES['image']['size'] > $maxFileSize) {
                http_response_code(CodeDeRetourApi::NoContent->value);

                throw new Exception('Error Processing Request', 1);
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (! \in_array($extension, $allowedExtensions)) {
                http_response_code(CodeDeRetourApi::BadRequest->value);
                throw new Exception('Error Processing Request', 1);
            }

            $check = getimagesize($file['tmp_name']);
            if ($check === false) {
                http_response_code(CodeDeRetourApi::Malicious->value);
                throw new Exception('Error Processing Request', 1);
            }

            $newName = uniqid('img_', true).'.'.$extension;
            $targetPath = "$targetDir$newName";

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $requete = '';
                if ($persnaliseRequete == null) {
                    $requete = "UPDATE $tableName SET image=? WHERE id_$tableName=?";
                } else {
                    $requete = $persnaliseRequete;
                }
                $api->execute([$newName, $id], $requete);
                http_response_code(CodeDeRetourApi::NoContent->value);

                $file = fopen($targetPath, 'r');
                $stream = new Stream($file);
                $fileSize = $stream->getSize();

                return $fileSize;
            } else {
                throw new Exception('Error Processing Request', 1);
            }
        } else {
            throw new Exception('Error Processing Request', 1);
        }
    }

    /**
     * Summary of GetGroupeImage
     */
    public static function GetImage(string $nom_image): string
    {
        try {
            $baseDir = dirname(__DIR__, 1).'/images';
            $fileName = file_exists("$baseDir/$nom_image") ? "$baseDir/$nom_image" : "$baseDir/defaultgroupe.png.jpeg";
            $filePath = $fileName;

            if (ob_get_level()) {
                ob_end_clean();
            }

            $fichier = fopen($filePath, 'a');

            fclose($fichier);

            return $filePath;

        } catch (Exception $e) {
            http_response_code(CodeDeRetourApi::InternalServerError->value);
            exit;
        }
    }

    /**
     * Summary of PaletteCreation
     *
     * @param  bool|resource  $image
     */
    public static function PaletteCreation(string $image, string $paletteName): int|bool
    {
        $finalImagePath = 'images/'.$paletteName;
        $paletteFile = fopen($finalImagePath, 'a');
        $imageFile = fopen($image, 'r');
        if (file_exists($finalImagePath) && file_exists($image)) {
            $imageFlux = fread($imageFile, filesize($image));
            $writtenFlux = fwrite($paletteFile, $imageFlux);

            return $writtenFlux;
        }

        return false;
    }
}
