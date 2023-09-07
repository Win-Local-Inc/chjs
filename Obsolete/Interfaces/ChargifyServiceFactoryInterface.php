<?php

namespace Obsolete\Interfaces;

use Obsolete\Chargify;
use Obsolete\Services\AbstractService;

interface ChargifyServiceFactoryInterface
{
    public function getService(Chargify $chargify, string $name): AbstractService;
}
