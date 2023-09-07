<?php

namespace Obsolete\Factories;

use Obsolete\Chargify;
use Obsolete\Exceptions\ClassNotExtendsAbstract;
use Obsolete\Exceptions\ClassNotFoundException;
use Obsolete\Interfaces\ChargifyServiceFactoryInterface;
use Obsolete\Services\AbstractService;

class ChargifyServiceFactory implements ChargifyServiceFactoryInterface
{
    protected static string $namespace = '\\App\\Services\\Chargify\\Services\\';

    protected array $services = [];

    public function getService(Chargify $chargify, string $name): AbstractService
    {
        $class = static::$namespace.ucfirst($name);

        if (array_key_exists($class, $this->services)) {
            return $this->services[$class];
        }

        if (! class_exists($class)) {
            throw new ClassNotFoundException($class);
        }

        if (get_parent_class($class) !== AbstractService::class) {
            throw new ClassNotExtendsAbstract(AbstractService::class);
        }

        return $this->services[$class] = new $class($chargify);
    }
}
