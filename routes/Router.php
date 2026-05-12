<?php

namespace Koyok\democratia\routes;

use Exception;
use Koyok\democratia\data\query\GroupeQuery;
use Koyok\democratia\data\query\InternauteQuery;
use Koyok\democratia\data\query\PropositionQuery;
use Koyok\democratia\data\query\ThematiqueQuery;
use Koyok\democratia\lib\CodeDeRetourApi;

final class Router
{
    public static array $queries = [
        'users' => new InternauteQuery,
        'groupes' => new GroupeQuery,
        'propositions' => new PropositionQuery,
        'thematiques' => new ThematiqueQuery,
    ];

    public array $parameters;

    public string $request;

    public function Routing(string $path, string $requestMethod): void
    {
        $requests = explode('/', $path);
        foreach (Router::$queries as $key => $value) {
            Router::$queries[$key] = $value->getQueries();
        }
        $tab = Router::$queries[$requests[0]][$requestMethod];
        if (empty($tab)) {
            return;
        } else {
            $this->ParameterWalking($tab, $requests, 0);
        }
    }

    private function ParameterWalking(array $tab, array $arrayPath, int $index): void
    {
        foreach ($tab as $key => $value) {
            if (\is_array($value)) {
                if (! empty($value['type'])) {
                    $filterTab = array_filter($value, fn ($clé) => $clé != 'type', ARRAY_FILTER_USE_KEY);
                    if (\array_key_exists($arrayPath[$index], $filterTab)) {
                        $filterPathTh = array_filter($arrayPath, fn ($clé) => $clé != 0, ARRAY_FILTER_USE_KEY);
                        $index += 1;
                        Router::ParameterWalking($filterTab[$arrayPath[$index]], $filterPathTh, $index);

                        return;
                    } elseif (str_contains($key, ':')) {
                        array_push($this->parameters, $arrayPath[$index]);
                        $index += 1;

                        return;
                    } else {
                        continue;
                    }
                } else {
                    $filterParam = array_filter($value, fn ($key) => $key != 2, ARRAY_FILTER_USE_KEY);
                    foreach ($filterParam as $indice => $param) {
                        array_push($this->parameters, $param);
                    }
                    $this->request = $value[2];

                    return;
                }
            }
        }
        throw new Exception('Chemin inexstant', CodeDeRetourApi::BadRequest->value);
    }
}
