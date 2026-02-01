<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Return a success JSON response
     */
    protected function successResponse($data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return an error JSON response
     */
    protected function errorResponse(string $message = 'Operation failed', $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a paginated success response
     */
    protected function paginatedResponse($paginatedData, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'items' => $paginatedData->items(),
                'pagination' => [
                    'current_page' => $paginatedData->currentPage(),
                    'per_page' => $paginatedData->perPage(),
                    'total' => $paginatedData->total(),
                    'last_page' => $paginatedData->lastPage(),
                    'from' => $paginatedData->firstItem(),
                    'to' => $paginatedData->lastItem(),
                    'first_page_url' => $paginatedData->url(1),
                    'last_page_url' => $paginatedData->url($paginatedData->lastPage()),
                    'next_page_url' => $paginatedData->nextPageUrl(),
                    'prev_page_url' => $paginatedData->previousPageUrl(),
                    'path' => $paginatedData->path(),
                ],
            ],
        ], 200);
    }
}
