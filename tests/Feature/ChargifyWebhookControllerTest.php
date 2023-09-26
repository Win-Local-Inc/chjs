<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Tests\TestCase;

class ChargifyWebhookControllerTest extends TestCase
{
    public function testChargifyWebhookSuccess(
    ) {
        $user = [
            'user_id' => Uuid::uuid4()->toString(),
            'firstname' => Uuid::uuid4()->toString(),
            'lastname' => Uuid::uuid4()->toString(),
            'email' => Uuid::uuid4()->toString().'@test.com',
        ];
        DB::table('users')->insert($user);

        $customerId = random_int(1000000, 9999999);
        $data = [
            'id' => random_int(1000000, 9999999),
            'event' => WebhookEvents::CustomerCreate->value,
            'payload' => [
                'customer' => [
                    'first_name' => $user['firstname'],
                    'last_name' => $user['lastname'],
                    'id' => $customerId,
                    'reference' => $user['user_id'],
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
        $hashLocal = hash_hmac('sha256', $queryBuild, config('chjs.shared_key'));
        $this->withHeaders([
            'X-Chargify-Webhook-Signature-Hmac-Sha-256' => $hashLocal,
        ]);
        $server = $this->transformHeadersToServerVars([]);
        $cookies = $this->prepareCookiesForRequest();
        $response = $this->call('POST', route('webhook.v2'), $data, $cookies, [], $server, $queryBuild);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'chargify_id' => $customerId,
            'user_id' => $user['user_id'],
        ]);
    }

    public function testChargifyWebhookSignatureFailure(
    ) {

        $data = [
            'id' => random_int(1000000, 9999999),
            'event' => WebhookEvents::CustomerCreate->value,
            'payload' => [],
        ];

        $queryBuild = http_build_query($data);
        $this->withHeaders([
            'X-Chargify-Webhook-Signature-Hmac-Sha-256' => 'WRONG SIGNATURE',
        ]);
        $server = $this->transformHeadersToServerVars([]);
        $cookies = $this->prepareCookiesForRequest();
        $response = $this->call('POST', route('webhook.v2'), $data, $cookies, [], $server, $queryBuild);

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
        $hashLocal = hash_hmac('sha256', $queryBuild, config('chjs.shared_key'));
        $this->withHeaders([
            'X-Chargify-Webhook-Signature-Hmac-Sha-256' => $hashLocal,
        ]);
        $server = $this->transformHeadersToServerVars([]);
        $cookies = $this->prepareCookiesForRequest();
        $response = $this->call('POST', route('webhook.v2'), $data, $cookies, [], $server, $queryBuild);

        $response->assertStatus(422);
    }
}
