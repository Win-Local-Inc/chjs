<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Rawaby88\Portal\Portal;

class ChargifyTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testChargifyTokenSuccess()
    {
        $user = User::factory()->create();

        Portal::actingAs(
            $user
        );

        $response = $this->getJson('/api/chargify/token');

        $response->assertStatus(201)
            ->assertJson(
                fn (AssertableJson $json) => $json->where('status', 201)
                    ->where('success', true)
                    ->has('data.token')
            );
    }
}
