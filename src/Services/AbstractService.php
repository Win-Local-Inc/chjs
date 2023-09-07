<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Chargify\ObjectTypes;
use WinLocalInc\Chjs\Chjs;

abstract class AbstractService
{
    protected PendingRequest $httpClient;
    public function __construct(protected Chjs $chargify)
    {
        $this->httpClient = $this->chargify->getClient();
    }

    public function getClient()
    {
        return $this->chargify->getClient();
    }


    protected function validatePayload(array $parameters, array $check)
    {
        Validator::make($parameters, $check)->validate();
    }


    public function request(string $path, string $method, array $parameters = []) :ChargifyObject|Collection
    {
        $response = $this->httpClient->$method(ltrim($path, '/') . '.json', $parameters);

        if ($response->successful()) {
            $jsonResponse = $response->json();

            if (is_array($jsonResponse)) {
                $className = ObjectTypes::getClassName($jsonResponse);

                if ($className) {
                    return ObjectTypes::resolve($jsonResponse, $className);
                }
            }

            return $response;
        }

        return $response->throw();
    }


    public function get(string $path, array $parameters = []): ChargifyObject|Collection
    {
        return $this->request($path, 'get', $parameters);
    }

    public function post(string $path, array $parameters = []): ChargifyObject|Collection
    {
        return $this->request($path, 'post', $parameters);
    }

    public function put(string $path, array $parameters = []): ChargifyObject|Collection
    {
        return $this->request($path, 'put', $parameters);
    }

    public function patch(string $path, array $parameters = []): ChargifyObject|Collection
    {
        return $this->request($path, 'patch', $parameters);
    }

    public function delete(string $path, array $parameters = []): ChargifyObject|Collection
    {
        return $this->request($path, 'delete', $parameters);
    }
}
