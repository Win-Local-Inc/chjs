<?php

namespace Obsolete\Services;

use Illuminate\Support\Facades\Validator;
use Obsolete\Chargify;
use Obsolete\ChargifyConfig;
use Obsolete\Interfaces\ChargifyHttpClientInterface;

abstract class AbstractService
{
    public function __construct(protected Chargify $chargify)
    {
    }

    public function getClient(): ChargifyHttpClientInterface
    {
        return $this->chargify->getClient();
    }

    public function getConfig(): ChargifyConfig
    {
        return $this->chargify->getConfig();
    }

    protected function validatePayload(array $parameters, array $check)
    {
        Validator::make($parameters, $check)->validate();
    }
}
