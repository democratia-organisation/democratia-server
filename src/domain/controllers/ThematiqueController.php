<?php

namespace Koyok\democratia\domain\controllers;

use Koyok\democratia\data\query\Api;
use Psr\Http\Message\ServerRequestInterface;

final class ThematiqueController
{
    private Api $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function GetAllGroupe(ServerRequestInterface $request): array
    {
        return $this->api->execute([], 'SELECT * FROM thematique ORDER BY id_thematique');
    }
}
