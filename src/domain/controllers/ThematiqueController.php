<?php

namespace Koyok\democratia\domain\controllers;

use Koyok\democratia\data\query\Api;
use Koyok\democratia\lib\CodeDeRetourApi;
use Laminas\Diactoros\Response;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class ThematiqueController
{
    public function __construct(private Api $api) {}

    public function GetAllTheme(ServerRequestInterface $request): array
    {
        return $this->api->execute([], 'SELECT * FROM thematique ORDER BY id_thematique');
    }

    public function CreerThematique(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->api->execute([json_decode($request->getBody()->getContents(), true)['nomThematique']], 'INSERT INTO thematique (nom_thematique) VALUES (?)');
        if ($response['sucess'] == true) {
            return new Response(CodeDeRetourApi::Created->value);
        } else {
            throw new \Exception('Error pour insérer la novelle thématique', CodeDeRetourApi::InternalServerError->value);
        }

    }
}
