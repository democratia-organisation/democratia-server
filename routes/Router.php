<?php

namespace Koyok\democratia\routes;

use Koyok\democratia\data\query\GroupeQuery;
use Koyok\democratia\data\query\InternauteQuery;
use Koyok\democratia\data\query\PropositionQuery;
use Koyok\democratia\data\query\ThematiqueQuery;

final class Router
{
    public array $queries;

    public string $parameters;

    public string $request;

    private ?array $requestParameters;

    public function __construct(?array $requestParameters = null)
    {
        $this->queries = [
            'users' => new InternauteQuery,
            'groupes' => new GroupeQuery,
            'propositions' => new PropositionQuery,
            'thematiques' => new ThematiqueQuery,
        ];
        $this->parameters = '';
        $this->request = '';
        $this->requestParameters = $requestParameters;
    }

    public function Routing(string $path, string $requestMethod): void
    {
        $requests = explode('/', $path);
        foreach ($this->queries as $key => $value) {
            $this->queries[$key] = $value->getQueries();
        }
        $tab = $this->queries[$requests[1]][$requestMethod];
        if (empty($tab)) {
            return;
        } else {
            $this->ParameterWalking($tab, $requests, 1);
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
                    break;
                }
            } else {
                break;
            }
        }
        $filterParam = $tab[$arrayPath[\count($arrayPath) - 1]];
        $arrayParam = [];
        foreach ($filterParam as $indice => $param) {
            if ($param !== '' && $indice !== (\count($tab))) {
                array_push($arrayParam, $param);
            }
        }
        if ($this->requestParameters !== null) {
            foreach ($this->requestParameters as $key => $value) {
                array_push($arrayParam, $value);
            }
        }
        $this->parameters = json_encode($arrayParam);
        $this->request = $filterParam[2];
    }
}
