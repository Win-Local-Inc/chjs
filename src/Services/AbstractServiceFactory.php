<?php

namespace WinLocalInc\Chjs\Services;

abstract class AbstractServiceFactory
{
    private $client;

    private $services;

    public function __construct($client)
    {
        $this->client = $client;
        $this->services = [];
    }

    abstract protected function getServiceClass(string $name): ?string;

    public function __get(string $name)
    {
        return $this->getService($name);
    }

    public function getService(string $name)
    {
        $serviceClass = $this->getServiceClass($name);
        if ($serviceClass !== null) {
            if (! array_key_exists($name, $this->services)) {
                $this->services[$name] = new $serviceClass($this->client);
            }

            return $this->services[$name];
        }

        \trigger_error('Undefined property: '.static::class.'::$'.$name);

        return null;
    }
}
