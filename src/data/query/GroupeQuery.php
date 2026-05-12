<?php

namespace Koyok\democratia\data\query;

final class GroupeQuery implements IQuery
{
    private array $queries;

    public function __construct()
    {
        $this->queries = [
            'GET' => [
                '' => ['', '', 'SELECT * FROM thematique ORDER BY id_thematique'],
                ':id_groupe' => [
                    'type' => 'string',
                    'thematiqueJoin' => ['', '', 'SELECT budget_thematique,
                            BIN_TO_UUID(tg.id_groupe) AS id_groupe,
                            tg.id_thematique,
                            nom_thematique,
                            g.budget
                        FROM theme_groupe tg
                            INNER JOIN thematique t ON tg.id_thematique = t.id_thematique
                            INNER JOIN groupe g ON g.id_groupe = tg.id_groupe  WHERE tg.id_groupe=UUID_TO_BIN(?,1)
                        '],
                    '' => ['', '', 'SELECT BIN_TO_UUID(g.id_groupe, 1) as id, nom_groupe, couleur_groupe, g.image, budget, nb_signalement, nbj_dft_discuss, nbj_dft_vote  FROM groupe g  INNER JOIN infos_membre ifo ON g.id_groupe = ifo.id_groupe WHERE ifo.id_internaute=?'],
                ],
                'obtenirImage/:url' => ['', '', ''],
            ],
            'POST' => [
                '' => ['', $_POST[0], 'INSERT INTO groupe (id_groupe,nom_groupe,couleur_groupe,budget,nbj_dft_vote,nbj_dft_discuss) VALUES (UUID_TO_BIN(?,0),?,?,?,?,?)'],
                ':id_thematique' => [
                    'type' => 'int',
                    'theme' => ['', $_POST[0], 'INSERT INTO theme_groupe (id_groupe, id_thematique, budget_thematique) VALUES (UUID_TO_BIN(?,0),?,?)'],
                    'infos' => ['', $_POST[0], 'INSERT INTO infos_membre (
                                id_groupe,
                                id_internaute,
                                id_role,
                                id_notification
                            )
                            VALUES (
                                UUID_TO_BIN(?,0),
                                ?,
                                ?,
                                ?
                            )
                        '],
                ],
                'publierImage' => [
                    ':id_groupe' => [
                        'type' => 'string',
                        '' => ['', '', 'UPDATE groupe SET image=? WHERE id_groupe=?'],
                    ],
                ],

            ],
            'PATCH' => [
            ],
            'DELETE' => [
            ],
        ];
    }

    public function getQueries(): array
    {
        return $this->queries;
    }
}
