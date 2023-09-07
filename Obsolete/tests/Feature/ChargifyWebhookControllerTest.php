<?php

namespace Tests\Feature;

use App\Models\Chargify\ChargifyEvent;
use App\Models\User;
use App\Services\Chargify\Enums\WebhookEvents;
use App\Services\Chargify\WebhookHandlers\CustomerUpsert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class ChargifyWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testChargifyWebhookSuccess(
    ) {
        $user = User::factory()->create();
        $customerId = random_int(1000000, 9999999);
        $data = [
            'id' => random_int(1000000, 9999999),
            'event' => WebhookEvents::CustomerCreate->value,
            'payload' => [
                'customer' => [
                    'email' => $user->email,
                    'first_name' => $user->firstname,
                    'last_name' => $user->lastname,
                    'id' => $customerId,
                    'created_at' => '2023-08-05 08:06:32 -0400',
                    'updated_at' => '2023-08-05 08:06:32 -0400',
                ],
                'site' => [
                    'id' => '83534',
                    'subdomain' => 'win-local',
                ],
            ],
        ];

        $queryBuild = http_build_query($data);
        $hashLocal = hash_hmac('sha256', $queryBuild, config('chargify.sharedKey'));
        $this->withHeaders([
            'X-Chargify-Webhook-Signature-Hmac-Sha-256' => $hashLocal,
        ]);
        $server = $this->transformHeadersToServerVars([]);
        $cookies = $this->prepareCookiesForRequest();
        $response = $this->call('POST', '/api/chargify/webhook', $data, $cookies, [], $server, $queryBuild);

        $response->assertStatus(200);
        $this->assertDatabaseHas('chargify_events', ['id' => $data['id']]);
        $this->assertDatabaseHas('chargify_customers', [
            'id' => $customerId,
            'user_id' => $user->user_id,
        ]);
    }

    public function testChargifyWebhookTheSameEventSuccess(
    ) {
        Queue::fake([
            CustomerUpsert::class,
        ]);

        $event = ChargifyEvent::factory()->create();

        $data = [
            'id' => $event->id,
            'event' => $event->event_name,
            'payload' => $event->payload,
        ];

        $queryBuild = http_build_query($data);
        $hashLocal = hash_hmac('sha256', $queryBuild, config('chargify.sharedKey'));
        $this->withHeaders([
            'X-Chargify-Webhook-Signature-Hmac-Sha-256' => $hashLocal,
        ]);
        $server = $this->transformHeadersToServerVars([]);
        $cookies = $this->prepareCookiesForRequest();
        $response = $this->call('POST', '/api/chargify/webhook', $data, $cookies, [], $server, $queryBuild);

        $response->assertStatus(200);
        Queue::assertPushed(CustomerUpsert::class, 0);
    }

    public function testChargifyWebhookSignatureFailure(
    ) {
        $event = ChargifyEvent::factory()->create();

        $data = [
            'id' => $event->id,
            'event' => $event->event_name,
            'payload' => $event->payload,
        ];

        $queryBuild = http_build_query($data);
        $this->withHeaders([
            'X-Chargify-Webhook-Signature-Hmac-Sha-256' => 'WRONG SIGNATURE',
        ]);
        $server = $this->transformHeadersToServerVars([]);
        $cookies = $this->prepareCookiesForRequest();
        $response = $this->call('POST', '/api/chargify/webhook', $data, $cookies, [], $server, $queryBuild);

        $response->assertStatus(403);
    }

    public function testChargifyWebhookDataKeyMissing(
    ) {
        $data = [
            'id' => str_shuffle('123456789'),
            'payload' => [
                'customer' => [
                    'id' => '0',
                ],
            ],
        ];

        $queryBuild = http_build_query($data);
        $hashLocal = hash_hmac('sha256', $queryBuild, config('chargify.sharedKey'));
        $this->withHeaders([
            'X-Chargify-Webhook-Signature-Hmac-Sha-256' => $hashLocal,
        ]);
        $server = $this->transformHeadersToServerVars([]);
        $cookies = $this->prepareCookiesForRequest();
        $response = $this->call('POST', '/api/chargify/webhook', $data, $cookies, [], $server, $queryBuild);

        $response->assertStatus(422);
    }
}
