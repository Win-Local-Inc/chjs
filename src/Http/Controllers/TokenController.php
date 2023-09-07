<?php

namespace WinLocalInc\Chjs\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;


class TokenController
{
    public function __invoke(): JsonResponse
    {
        $payload = [
            'iss' => config('chjs.public_key'),
            'jti' =>  Str::random(32),
            'sub' =>  Str::random(32),
        ];

        $jwt = JWT::encode($payload, config('chjs.private_key'), 'HS256');

        return responder()
            ->success(['token' => $jwt])
            ->respond(201);
    }

}
