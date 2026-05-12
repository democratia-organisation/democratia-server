<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\data\query\GroupeQuery;
use Koyok\democratia\data\query\InternauteQuery;
use Koyok\democratia\data\query\PropositionQuery;
use Koyok\democratia\data\query\ThematiqueQuery;

final class Router
{
    public static array $queries = [
        'users' => new InternauteQuery,
        'groupes' => new GroupeQuery,
        'propositions' => new PropositionQuery,
        'thematiques' => new ThematiqueQuery,
    ];

    public static function Routing(string $path, string $requestMethod): mixed
    {
        $requests = explode('/', $path);
        foreach (Router::$queries as $key => $value) {
            Router::$queries[$key] = $value->getQueries();
        }
        $tab = Router::$queries[$requests[0]][$requestMethod];
        if (empty($tab)) {
            return null;
        } else {
            // explorer récursivement jusqu'à trouver le bon chemin
            // mettre les valeurs nécessaie dans $_GET["request"] et $_GET["parameters"]
            return $tab[0];
        }
    }
}
