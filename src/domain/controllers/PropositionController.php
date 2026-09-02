<?php

namespace Koyok\democratia\domain\controllers;

use Koyok\democratia\data\query\Api;
use Psr\Http\Message\ServerRequestInterface;

final class PropositionController
{
    public function __construct(private Api $api) {}

    public function GetPropostionsDUnGroupe(ServerRequestInterface $request, array $args): array
    {
        return $this->api->execute([$args['idGroupe']], 'SELECT BIN_TO_UUID(id_groupe,1) AS id_groupe,id_proposition budget, date_publication, description_proposition, id_proposition, id_thematique, nb_signalement, titre_proposition FROM proposition WHERE id_groupe = UUID_TO_BIN(?,1)');
    }
}
