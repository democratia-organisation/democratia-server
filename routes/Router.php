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
        foreach ($this->queries as $key => $value) {
            $this->queries[$key] = $value->getQueries();
        }
        $this->parameters = '';
        $this->request = '';
        $this->requestParameters = $requestParameters;
    }

    public function Routing(string $path, string $requestMethod): void
    {
        $requests = explode('/', $path);
        $tab = $this->queries[$requests[1]][$requestMethod];
        if (empty($tab)) {
            return;
        } else {
            $requests = \array_slice($requests, 1);
            $this->ParameterWalking($tab, $requests, 1);
        }
    }

    private function ParameterWalking(array $tab, array $arrayPath, int $index): void
    {
        if (empty($tab[$arrayPath[$index]])) {
            return;
        } else {
            if (\count($tab[$arrayPath[$index]]) === 1) {
                $this->EndWalking($tab, $arrayPath);

                return;
            } elseif (\count($tab[$arrayPath[$index]]) === 2) {
                $index += 1;
                $this->ParametersDescente($tab, $arrayPath, $index);

                return;
            } else {
                $index += 1;
                $arrayPath = \array_slice($arrayPath, 1);
                $tab = $tab[$arrayPath[$index]];
                $this->ParameterWalking($tab, $arrayPath, $index);

                return;
            }
        }
    }

    private function TypeFiltering(string $typeName, mixed $var): bool
    {
        return is_a($var, $typeName);
    }

    private function EndWalking(array $tab, array $arrayPath): void
    {
        $filterParam = $tab[$arrayPath[\count($arrayPath) - 1]];
        $arrayParam = [];
        foreach ($filterParam as $indice => $param) {
            if ($param !== '' && $indice !== (\count($filterParam) - 1)) {
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

    private function ParametersDescente(array $value, array $arrayPath, int $index): void
    {
        $key = array_keys($value)[0];
        if (! $this->TypeFiltering($value[$key]['type'], $arrayPath[0])) {
            throw new Exception('Error Processing Request', CodeDeRetourApi::BadRequest->value);
        } else {
            array_push($this->parameters, $arrayPath[0]);
            $this->EndWalking($value, $arrayPath);

            return;
        }

    }
}
