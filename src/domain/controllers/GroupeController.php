<?php

namespace Koyok\democratia\domain\controllers;

use Koyok\democratia\data\query\Api;
use Koyok\democratia\lib\{CodeDeRetourApi, ImageManager};
use Laminas\Diactoros\{Response, Stream};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class GroupeController
{
    private Api $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function GetThematiquesDUnGroupe(ServerRequestInterface $request, array $args): array
    {
        return $this->api->execute([$args['idGroupe']], 'SELECT budget_thematique, BIN_TO_UUID(tg.id_groupe,1) AS id_groupe, tg.id_thematique, nom_thematique, g.budget FROM theme_groupe tg INNER JOIN thematique t ON tg.id_thematique = t.id_thematique INNER JOIN groupe g ON g.id_groupe = tg.id_groupe  WHERE tg.id_groupe=UUID_TO_BIN(?,1)');
    }

    public function GetImageDeGroupe(ServerRequestInterface $request, array $args): ResponseInterface
    {

        $paletteTitle = '';
        $result = $this->api->execute([$args['idInternaute']], 'SELECT bin_to_uuid(g.id_groupe, 1) AS id_groupe, g.image, id_internaute FROM groupe g INNER JOIN infos_membre ifo ON ifo.id_groupe = g.id_groupe WHERE ifo.id_internaute = ?;');
        foreach ($result['data'] as $key => $data) {
            $fichierPath = ImageManager::GetImage($data['image']);
            if ($key == 0) {
                $paletteTitle = 'groupes_image_of_'.$data['id_internaute'];
            }
            $imageSize = ImageManager::PaletteCreation($fichierPath, $paletteTitle);
            if (\is_bool($imageSize)) {
                throw new \Exception('Error Processing Request', CodeDeRetourApi::InternalServerError->value);
            }
        }
        $response = new Response;
        $file = fopen("images/$paletteTitle", 'r');
        $stream = new Stream($file);
        $fileSize = $stream->getSize();

        return $response
            ->withHeader('Content-Type', mime_content_type($file))
            ->withHeader('Content-Length', $fileSize)
            ->withBody($stream);

    }

    public function GetGroupe(ServerRequestInterface $request, array $args): array
    {
        return $this->api->execute([$args['idInternaute']], 'SELECT BIN_TO_UUID(g.id_groupe, 1) as id_groupe, nom_groupe, couleur_groupe, g.image, budget, nb_signalement, nbj_dft_discuss, nbj_dft_vote, image_size, ifo.id_role  FROM groupe g  INNER JOIN infos_membre ifo ON g.id_groupe = ifo.id_groupe WHERE ifo.id_internaute=uuid_to_bin(?,1)');
    }

    public function AjouterGroupe(ServerRequestInterface $request): array
    {
        return $this->api->execute($_POST, 'INSERT INTO groupe (id_groupe,nom_groupe,couleur_groupe,budget,nbj_dft_vote,nbj_dft_discuss) VALUES (UUID_TO_BIN(?,1),?,?,?,?,?)');
    }

    public function AjouterTheme(ServerRequestInterface $request): array
    {
        return $this->api->execute($_POST, 'INSERT INTO theme_groupe (id_groupe, id_thematique, budget_thematique) VALUES (UUID_TO_BIN(?,1),?,?)');
    }

    public function AjouterInternaute(ServerRequestInterface $request): array
    {
        return $this->api->execute($_POST, 'INSERT INTO infos_membre (id_groupe,id_internaute,id_role,id_notification)VALUES (UUID_TO_BIN(?,1),?,?,?');
    }

    public function PublierImageGroupe(ServerRequestInterface $request, array $args): array
    {
        $imageSize = ImageManager::UploadImage($args['idGroupe'], 'groupe', 'UPDATE groupe SET image=? WHERE id_groupe=UUID_TO_BIN(?, 0)');
        $this->api->execute([$imageSize, $args['id_groupe']], 'UPDATE groupe SET image_size=? WHERE id_groupe=UUID_TO_BIN(?,1)');

        return [];
    }

    public function GetRole(ServerRequestInterface $request, array $args): array
    {
        return $this->api->execute([$args['idInternaute']], 'SELECT ');
    }
}
