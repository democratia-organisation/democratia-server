<?php

namespace Koyok\democratia\domain\controllers;

use Koyok\democratia\data\query\Api;
use Koyok\democratia\lib\DeleteMethode;
use Psr\Http\Message\ServerRequestInterface;

// TODO : pour implémenter le query route
/**
 * [<queryPart>] => [
 * "<Method>" => [
 * "{{:}<path>}"  => [{namedParameters}, {dataParameters}  ,"<sql request>", ]
 * ]
 * ]
 */
final class InternauteController
{
    public array $queries;

    private Api $api;

    public function GetGroupe(ServerRequestInterface $request, array $args): array
    {
        // tableau recré afin d'avoir une clé qui soit 0 et non idInternaute
        return $this->api->execute([$args['idInternaute']], 'SELECT BIN_TO_UUID(g.id_groupe) AS id_groupe, nom_groupe, budget, couleur_groupe, image, nb_signalement, nbj_dft_discuss, nbj_dft_vote FROM groupe g INNER JOIN infos_membre ifo ON g.id_groupe = ifo.id_groupe WHERE id_internaute=?');
    }

    public function GetMailDoublon(ServerRequestInterface $request, array $args): array
    {
        return $this->api->execute([$args['email']], 'SELECT COUNT(courriel) FROM internaute WHERE courriel=?');
    }

    public function Login(ServerRequestInterface $request): array
    {
        return $this->api->execute($_POST, 'SELECT * FROM internaute WHERE courriel=? AND hashageMDP=?');
    }

    public function SupprimerInternaute(ServerRequestInterface $request, array $args): array
    {
        $requete = $this->api->tryGetAction('SupprimerInternaute', DeleteMethode::class);

        return $this->api->execute([$args['idInternaute']], $requete);
    }

    public function __construct(Api $api)
    {
        $this->api = $api;
        $this->queries = [
            'GET' => [

                '' => [
                    ':id_internuate' => ['type' => 'int'],
                    ['', '', 'SELECT * FROM internaute WHERE id_internaute=?'],
                ],
                'doublon' => [
                    ':courriel' => ['type' => 'string'],
                    ['', '', 'SELECT COUNT(courriel) FROM internaute WHERE courriel=?'],
                ],

            ],
            'POST' => [
                '' => ['', '', 'CreerUtilisateur'],
                'login' => ['', '', 'SELECT * FROM internaute WHERE courriel=? AND hashageMDP=?'],
                'refresh' => ['', '', 'refresh'],
            ],
            'PATCH' => [
                '' => ['', '', 'ModifInfoInternaute'],
            ],
            'DELETE' => [
                '' => [
                    ':id_internaute' => ['type' => 'int'],
                    ['', '', 'SupprimerInternaute'],
                ],

            ],
        ];
    }

    public function getQueries(): array
    {
        return $this->queries;
    }
}
