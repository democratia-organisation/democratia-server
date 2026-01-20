<?php
// Dossier de destination

require_once 'ClassRest.php';

function UploadGroupeImage(int $id_groupe) : void  {
    $api = new Api();
    $targetDir = __DIR__ . "/uploads/";
    $maxFileSize = 2 * 1024 * 1024; // Limite à 2 Mo

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
        $file = $_FILES['image'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // 1. Vérification de l'extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            http_response_code(CodeDeRetourApi::BadRequest->value);
            die(json_encode(["success" => false, "message" => "Format non autorisé", "status" => CodeDeRetourApi::BadRequest->value]));
        }
    
        // 2. Vérification du vrai type MIME (sécurité contre les faux fichiers)
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            http_response_code(CodeDeRetourApi::Malicious->value);
            die(json_encode(["success" => false, "message" => "Le fichier n'est pas une image réelle", "status" => CodeDeRetourApi::Malicious->value] ));
        }
    
        // 3. Génération d'un nom unique (pour éviter les doublons et cacher le nom original)
        $newName = uniqid('img_', true) . "." . $extension;
        $targetPath = $targetDir . $newName;
    
        // 4. Déplacement du fichier
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $api->patch([$newName,$id_groupe],"UPDATE groupe SET image=? WHERE id_groupe=?");
            http_response_code(CodeDeRetourApi::OK->value);
            echo json_encode(["success" => true, "data" => [], "status" => CodeDeRetourApi::OK->value]);
            exit;
        } else {
            http_response_code(CodeDeRetourApi::InternalServerError->value);
            echo json_encode(["success" => false, "message" => "Erreur lors du transfert", 'status' => CodeDeRetourApi::InternalServerError->value]);
            exit;
        }
    }
}

function GetGroupeImage(int $id_groupe): void {
    $api = new Api();
    try {
        $api->get([$id_groupe], "SELECT image FROM groupe WHERE id_groupe = ?");

        // 2. Définition du chemin (nom par défaut si vide ou non trouvé)
        $fileName = ($api->getTabRetour() && !empty($api->getTabRetour()['image'])) ? $api->getTabRetour()['image'] : 'default-groupe.png.jpeg';
        $filePath = __DIR__ . "/uploads/" . $fileName;

        // 3. Si même le fichier par défaut est manquant, on arrête
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo json_encode(["error" => "Image physique introuvable"]);
            return;
        }

        // 4. Détection du type MIME (image/jpeg, image/png, etc.)
        $mimeType = mime_content_type($filePath);
        
        // 5. Nettoyage du tampon pour envoyer uniquement les données binaires
        if (ob_get_level()) ob_end_clean();
        
        // 6. Envoi des headers HTTP
        header("Content-Type: $mimeType");
        header("Content-Length: " . filesize($filePath));

        // 7. Lecture et envoi du fichier
        readfile($filePath);
        exit;

    } catch (Exception $e) {
        http_response_code(CodeDeRetourApi::InternalServerError->value);
        echo json_encode(["error" => $e->getMessage()]);
    }
}