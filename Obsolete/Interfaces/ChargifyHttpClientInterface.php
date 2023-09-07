<?php

namespace Obsolete\Interfaces;

use Illuminate\Http\Client\Response;

interface ChargifyHttpClientInterface
{
    public function request(
        string $path,
        string $method,
        array $parameters = []
    ): Response;
}
