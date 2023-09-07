<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;

class ChargifySubscriptionStatusServiceTest extends TestCase
{
    public function testSubscriptionStatusServiceCancelSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription' => [
                        'id' => $subscriptionId,
                        'state' => 'canceled',
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->subscriptionStatus()->cancelSubscription($subscriptionId);

        $this->assertEquals($subscriptionId, $response['id']);
    }

    public function testSubscriptionStatusServiceReactivateSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription' => [
                        'id' => $subscriptionId,
                        'state' => 'active',
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->subscriptionStatus()->reactivateSubscription($subscriptionId);

        $this->assertEquals($subscriptionId, $response['id']);
    }
}
