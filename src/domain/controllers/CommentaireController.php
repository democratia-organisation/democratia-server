<?php

namespace Koyok\democratia\domain\controllers;

use GuzzleHttp\Psr7\Response;
use Koyok\democratia\data\query\Api;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

final class CommentaireController
{
    private Api $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function GetMessageFromProposition(ServerRequestInterface $request, array $args): array
    {
        return $this->api->execute(array_values($args), 'SELECT c.id_commentaire, contenu_message, horodatage, nb_signalement, prenom_internaute, nom_internaute, r.id_role, ifo.id_internaute FROM commentaire c INNER JOIN internaute i ON c.id_internaute = i.id_internaute INNER JOIN infos_membre ifo ON c.id_internaute = ifo.id_internaute INNER JOIN role r ON ifo.id_role = r.id_role WHERE c.id_groupe = uuid_to_bin(?,1) AND id_proposition = ?');
    }

    public function PostMessage(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response;
        $contenu = $this->api->execute($_POST, 'INSERT INTO commentaire (contenu_message,horodatage,id_groupe,id_internaute,id_proposition)VALUES (?,?,?,?,?);');
        if ($contenu['success'] == true) {
            $response = $response->withStatus($contenu['code']);
        }

        return $response;
    }
}
