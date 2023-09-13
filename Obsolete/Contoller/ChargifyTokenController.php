<?php

namespace App\Http\Controllers\Chargify;

use App\Services\Chargify\ChargifyConfig;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class ChargifyTokenController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $config = $this->getChargifyConfig();

        $payload = [
            'iss' => $config->getPublicKey(),
            'jti' => Str::random(32),
            'sub' => Str::random(32),
        ];

        $jwt = JWT::encode($payload, $config->getPrivateKey(), 'HS256');

        return responder()
            ->success(['token' => $jwt])
            ->respond(HTTP_CREATED);
    }

    protected function getChargifyConfig(): ChargifyConfig
    {
        return resolve(ChargifyConfig::class);
    }
}
