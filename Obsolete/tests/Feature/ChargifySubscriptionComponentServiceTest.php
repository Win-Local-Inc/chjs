<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;

class ChargifySubscriptionComponentServiceTest extends TestCase
{
    public function testSubscriptionComponentServiceGetSubscriptionComponentSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);
        $componentId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'component' => [
                        'component_id' => $componentId,
                        'subscription_id' => $subscriptionId,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->subscriptionComponent()
            ->getSubscriptionComponent($subscriptionId, $componentId);

        $this->assertEquals($subscriptionId, $response['subscription_id']);
        $this->assertEquals($componentId, $response['component_id']);
    }

    public function testSubscriptionComponentServiceListSubscriptionComponentsSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);
        $componentId1 = random_int(10000000, 99999999);
        $componentId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'component' => [
                        'component_id' => $componentId1,
                    ]],
                    ['component' => [
                        'component_id' => $componentId2,
                    ]],
                ], 200),
        ]);

        $collection = $this->getChargify()->subscriptionComponent()
            ->listSubscriptionComponents($subscriptionId);

        $this->assertTrue($collection->contains('component_id', '=', $componentId1));
        $this->assertTrue($collection->contains('component_id', '=', $componentId2));
    }
}
