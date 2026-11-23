<?php

namespace App\Docs\Strategies;

use Knuckles\Scribe\Extracting\Strategies\Strategy;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use ReflectionClass;

class ExtractQueryBuilderParams extends Strategy
{
    public function __invoke(ExtractedEndpointData $endpointData, array $settings = []): array
    {
        // Only apply to GET requests
        if (!in_array('GET', $endpointData->httpMethods)) {
            return [];
        }

        $reflection = $endpointData->controller;

        if (!$reflection instanceof ReflectionClass) {
            $controllerClass = $endpointData->controllerName ?? null;
            if (!$controllerClass && is_object($endpointData->controller)) {
                $controllerClass = get_class($endpointData->controller);
            }
            if (!$controllerClass && is_string($endpointData->controller)) {
                $controllerClass = $endpointData->controller;
            }
            if ($controllerClass) {
                try {
                    $reflection = new ReflectionClass($controllerClass);
                } catch (\ReflectionException $e) {
                    return [];
                }
            } else {
                return [];
            }
        }

        $params = [];

        if ($reflection->hasConstant('ALLOWED_INCLUDES')) {
            $includes = $reflection->getConstant('ALLOWED_INCLUDES');
            if (is_array($includes) && !empty($includes)) {
                $params['include'] = [
                    'name' => 'include',
                    'description' => 'Comma-separated list of related resources to include. <br>Available: <code>' . implode('</code>, <code>', $includes) . '</code>',
                    'required' => false,
                    'example' => $includes[0] ?? null,
                    'type' => 'string',
                ];
            }
        }

        if ($reflection->hasConstant('ALLOWED_FILTERS')) {
            $filters = $reflection->getConstant('ALLOWED_FILTERS');
            if (is_array($filters)) {
                foreach ($filters as $filter) {
                    $filterName = null;
                    if (is_string($filter)) {
                        $filterName = $filter;
                    } elseif (is_object($filter) && method_exists($filter, 'getName')) {
                        $filterName = $filter->getName();
                    }

                    if ($filterName) {
                        $params["filter[$filterName]"] = [
                            'name' => "filter[$filterName]",
                            'description' => "Filter results by $filterName",
                            'required' => false,
                            'example' => null,
                            'type' => 'string',
                        ];
                    }
                }
            }
        }

        return $params;
    }
}
