<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifyPaymentProfileServiceTest extends TestCase
{
    public function testPaymentProfileServiceCreateSuccess()
    {
        $paymentProfileId = random_int(10000000, 99999999);
        $cutomerId = random_int(10000000, 99999999);
        $chargifyToken = 'tok_'.Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'payment_profile' => [
                        'id' => $paymentProfileId,
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->paymentProfile()->create($cutomerId, $chargifyToken);

        $this->assertEquals($paymentProfileId, $response['id']);
    }

    public function testPaymentProfileServiceUpdateSuccess()
    {
        $paymentProfileId = random_int(10000000, 99999999);
        $firstName = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'payment_profile' => [
                        'first_name' => $firstName,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->paymentProfile()->update($paymentProfileId, [
            'first_name' => $firstName,
        ]);

        $this->assertEquals($firstName, $response['first_name']);
    }

    public function testPaymentProfileServiceDeleteSuccess()
    {
        $paymentProfileId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([], 204),
        ]);

        $this->getChargify()->paymentProfile()->delete($paymentProfileId);

        Http::assertSentCount(1);
    }

    public function testPaymentProfileServiceListPaymentProfilesSuccess()
    {
        $paymentProfileId1 = random_int(10000000, 99999999);
        $paymentProfileId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'payment_profile' => [
                        'id' => $paymentProfileId1,
                    ],
                ], [
                    'payment_profile' => [
                        'id' => $paymentProfileId2,
                    ],
                ],
                ], 200),
        ]);

        $collection = $this->getChargify()->paymentProfile()->listPaymentProfiles([
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertTrue($collection->contains('id', '=', $paymentProfileId1));
        $this->assertTrue($collection->contains('id', '=', $paymentProfileId2));
    }

    public function testPaymentProfileServiceGetByIdSuccess()
    {
        $paymentProfileId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'payment_profile' => [
                        'id' => $paymentProfileId,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->paymentProfile()->getPaymentProfileById($paymentProfileId);

        $this->assertEquals($paymentProfileId, $response['id']);
    }
}
