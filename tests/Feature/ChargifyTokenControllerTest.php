<?php

namespace WinLocalInc\Chjs\Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use WinLocalInc\Chjs\Tests\TestCase;

class ChargifyTokenControllerTest extends TestCase
{
    public function testChargifyTokenSuccess()
    {
        $response = $this->getJson(route('chargify.token'));

        $response->assertStatus(201)
            ->assertJson(
                fn (AssertableJson $json) => $json->where('status', 201)
                    ->where('success', true)
                    ->has('data.token')
            );
    }
}
