<?php

namespace Koyok\democratia\data\query;

final class PropositionQuery implements IQuery
{
    public array $queries;

    public function __construct()
    {
        $this->queries = [
            'GET' => [
                ':id_groupe' => ['', '', 'SELECT BIN_TO_UUID(id_groupe) AS id_groupe,id_proposition
                            budget,
                            date_publication,
                            description_proposition,
                            id_proposition,
                            id_thematique,
                            nb_signalement,
                            titre_proposition
                        FROM proposition
                        WHERE id_groupe = UUID_TO_BIN(?)
                        '],
            ],
            'POST' => [
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
