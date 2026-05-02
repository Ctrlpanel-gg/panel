<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ApiResponseService
{
    public static function success(mixed $data = null, ?array $meta = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
            'error' => null,
            'meta' => $meta,
        ];

        if ($data instanceof JsonResource) {
            $response['data'] = $data->resolve();
            $response['meta'] = $meta ?? self::extractPaginationMeta($data);
        } elseif ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->items();
            $response['meta'] = $meta ?? self::buildPaginationMeta($data);
        } elseif (is_iterable($data) && !is_array($data)) {
            $data = collect($data);
            $response['data'] = $data->toArray();
        }

        return response()->json($response, $statusCode);
    }

    public static function created(mixed $data = null, ?array $meta = null): JsonResponse
    {
        return self::success($data, $meta, 201);
    }

    public static function error(string $code, string $message, int $statusCode = 400, ?array $meta = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
            'meta' => $meta,
        ], $statusCode);
    }

    public static function noContent(): JsonResponse
    {
        return response()->noContent();
    }

    private static function extractPaginationMeta(JsonResource $resource): ?array
    {
        $underlyingResource = $resource->resource ?? null;
        if ($underlyingResource instanceof LengthAwarePaginator) {
            return self::buildPaginationMeta($underlyingResource);
        }
        return null;
    }

    private static function buildPaginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
