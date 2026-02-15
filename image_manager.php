<?php


require_once 'ClassRest.php';

function UploadGroupeImage(int $id_groupe) : void  {
    $api = new Api();
    $targetDir = __DIR__ . "/images/";
    $maxFileSize = 10 * 1024 * 1024; 

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) { 
        $file = $_FILES['image'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($_FILES['image']['size']>$maxFileSize) {
            http_response_code(CodeDeRetourApi::NoContent->value);
            die(json_encode(["success" => false, "message" => "Fichier trop grop", "status" => CodeDeRetourApi::NoContent->value]));
        }
        
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            http_response_code(CodeDeRetourApi::BadRequest->value);
            die(json_encode(["success" => false, "message" => "Format non autorisé", "status" => CodeDeRetourApi::BadRequest->value]));
        }
    
        
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            http_response_code(CodeDeRetourApi::Malicious->value);
            die(json_encode(["success" => false, "message" => "Le fichier n'est pas une image réelle", "status" => CodeDeRetourApi::Malicious->value] ));
        }
    
        
        $newName = uniqid('img_', true) . "." . $extension;
        $targetPath = $targetDir . $newName;
    
        
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

        
        $fileName = ($api->getTabRetour() && !empty($api->getTabRetour()['image'])) ? $api->getTabRetour()['image'] : 'default-groupe.png.jpeg';
        $filePath = __DIR__ . "/images/" . $fileName;

        
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo json_encode(["error" => "Image physique introuvable"]);
            return;
        }

        
        $mimeType = mime_content_type($filePath);
        
        
        if (ob_get_level()) ob_end_clean();
        
        
        header("Content-Type: $mimeType");
        header("Content-Length: " . filesize($filePath));

        
        readfile($filePath);
        exit;

    } catch (Exception $e) {
        http_response_code(CodeDeRetourApi::InternalServerError->value);
        echo json_encode(["error" => $e->getMessage()]);
    }
}