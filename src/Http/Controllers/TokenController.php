<?php

namespace WinLocalInc\Chjs\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TokenController
{
    public function __invoke(): JsonResponse
    {
        $payload = [
            'iss' => config('chjs.public_key'),
            'jti' => Str::random(32),
            'sub' => Str::random(32),
        ];

        $jwt = JWT::encode($payload, config('chjs.private_key'), 'HS256');

        return new JsonResponse([
            'status' => 201,
            'success' => true,
            'data' => [
                'token' => $jwt,
            ],
        ], 201);
    }
}
