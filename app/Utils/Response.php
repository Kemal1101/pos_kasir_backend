<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Response
{
    public static function success($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'meta' => [
                'status' => $status,
                'message' => $message,
            ],
            'data' => $data,
        ], $status);
    }

    public static function error(?Throwable $error = null, string $message = 'Something went wrong', int $status = 500): JsonResponse
    {
        // handle validation errors
        if ($error instanceof ValidationException) {
            return response()->json([
                'meta' => [
                    'status' => 400,
                    'message' => $message,
                ],
                'data' => $error->errors(),
            ], 400);
        }

        // model not found
        if ($error instanceof ModelNotFoundException) {
            return response()->json([
                'meta' => [
                    'status' => 404,
                    'message' => 'Data not found',
                ],
                'data' => null,
            ], 404);
        }

        // unauthorized
        if ($error instanceof AuthenticationException) {
            return response()->json([
                'meta' => [
                    'status' => 403,
                    'message' => 'Unauthorized',
                ],
                'data' => null,
            ], 403);
        }

        // fallback
        return response()->json([
            'meta' => [
                'status' => $status,
                'message' => $message,
            ],
            'data' => [
                'error' => $error ? $error->getMessage() : null,
            ],
        ], $status);
    }

    public static function notFound(string $message = 'Not Found'): JsonResponse
    {
        return response()->json([
            'meta' => [
                'status' => 404,
                'message' => $message,
            ],
            'data' => null,
        ], 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return response()->json([
            'meta' => [
                'status' => 403,
                'message' => $message,
            ],
            'data' => null,
        ], 403);
    }

    public static function token(string $token, $user = null, ?int $expires = null): JsonResponse
    {
        return response()->json([
            'meta' => [
                'status' => 200,
                'message' => 'Login berhasil',
            ],
            'data' => [
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => $expires,
                'user'         => $user,
            ],
            'errors' => null,
        ], 200);
    }

    public static function pagination($data, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'meta' => [
                'status'  => $status,
                'message' => $message,
            ],
            'data' => $data['items'],
            'pagination' => [
                'page'         => $data['page'],
                'limit'        => $data['limit'],
                'total'        => $data['total'],
                'last_page'    => $data['last_page'],
                'next_page'    => $data['next_page'],
                'prev_page'    => $data['prev_page'],
            ]
        ], $status);
    }
}
