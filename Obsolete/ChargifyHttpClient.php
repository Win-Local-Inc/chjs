<?php

namespace Obsolete;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Obsolete\Interfaces\ChargifyHttpClientInterface;

class ChargifyHttpClient implements ChargifyHttpClientInterface
{
    protected PendingRequest $httpClient;

    public function __construct(
        protected ChargifyConfig $config,
    ) {
        $this->httpClient = Http::acceptJson()
            ->asJson()
            ->baseUrl($config->getHostname())
            ->timeout($config->getTimeout())
            ->withBasicAuth($config->getApiKey(), 'x');
    }

    /*
    * @throws \Illuminate\Http\Client\RequestException
    */
    public function request(
        string $path,
        string $method,
        array $parameters = []
    ): Response {
        return $this->httpClient->$method(ltrim($path, '/').'.json', $parameters)->throw();
    }
}
