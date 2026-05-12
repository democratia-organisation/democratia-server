<?php

namespace Koyok\democratia\data\query;

final class ThematiqueQuery implements IQuery
{
    public array $queries;

    public function __construct()
    {
        $this->queries = [
            'GET' => [
                '' => ['', '', 'SELECT * FROM thematique ORDER BY id_thematique'],
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
