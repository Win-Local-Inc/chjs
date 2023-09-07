<?php

namespace Tests\Feature;

use App\Services\Chargify\Chargify;
use App\Services\Chargify\ChargifySystem;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function getChargify(): Chargify
    {
        return resolve(Chargify::class);
    }

    public function getChargifySystem(): ChargifySystem
    {
        return resolve(ChargifySystem::class);
    }
}
