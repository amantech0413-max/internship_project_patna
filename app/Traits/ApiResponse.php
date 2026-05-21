<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data instanceof ResourceCollection) {
            $payload['data'] = $data->resolve();
            $paginator = $data->resource;
            if ($paginator instanceof LengthAwarePaginator) {
                $payload['meta'] = [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ];
            }
        } elseif ($data instanceof JsonResource) {
            $payload['data'] = $data->resolve();
        } elseif ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $code);
    }

    protected function error(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $code);
    }
}
