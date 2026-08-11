<?php

namespace Koyok\democratia\domain\controllers;

use GuzzleHttp\Psr7\Response;
use Koyok\democratia\data\query\Api;
use Koyok\democratia\lib\CodeDeRetourApi;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class ThematiqueController
{
    private Api $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function GetAllTheme(ServerRequestInterface $request): array
    {
        return $this->api->execute([], 'SELECT * FROM thematique ORDER BY id_thematique');
    }

    public function CreerThematique(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->api->execute([, $_POST['nomThematique']], 'INSERT INTO thematique (nom_thematique) VALUES (?)');
        if ($response['success'] == true) {
            return new Response(CodeDeRetourApi::Created->value);
        } else {
            throw new \Exception('Error pour insérer la novelle thématique', CodeDeRetourApi::InternalServerError->value);
        }

    }
}
