<?php

namespace Koyok\democratia\data\query;

// TODO : pour implémenter le query route
/**
 * [<queryPart>] => [
 * "<Method>" => [
 * "{{:}<path>}"  => [{namedParameters}, {dataParameters}  ,"<sql request>", ]
 * ]
 * ]
 */
final class InternauteQuery implements IQuery
{
    public array $queries;

    public function __construct()
    {
        $this->queries = [
            'GET' => [
                ':id_internuate' => [
                    'type' => 'int',
                    '' => ['', '', 'SELECT * FROM internaute WHERE id_internaute=?'],
                    'groupes' => ['', '', 'SELECT BIN_TO_UUID(g.id_groupe) AS id_groupe, nom_groupe, budget, couleur_groupe, image, nb_signalement, nbj_dft_discuss, nbj_dft_vote FROM groupe g INNER JOIN infos_membre ifo ON g.id_groupe = ifo.id_groupe WHERE id_internaute=?'],
                ],
                ':courriel' => [
                    'type' => 'string',
                    'doublon' => ['', '', 'SELECT COUNT(courriel) FROM internaute WHERE courriel=?'],
                ],
            ],
            'POST' => [
                '' => ['', $_POST['0'], 'CreerUtilisateur'],
            ],
            'PATCH' => [
                '' => ['', '', 'ModifInfoInternaute'],
            ],
            'DELETE' => [
                ':id_internaute' => [
                    'type' => 'int',
                    '' => ['', '', 'SupprimerInternaute'],
                ],
            ],
        ];
    }

    public function getQueries(): array
    {
        return $this->queries;
    }
}
