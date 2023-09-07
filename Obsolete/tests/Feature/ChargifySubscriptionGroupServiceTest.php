<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifySubscriptionGroupServiceTest extends TestCase
{
    public function testSubscriptionGroupServiceCreateSuccess()
    {
        $customerId = random_int(10000000, 99999999);
        $subscriptionId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription_group' => [
                        'customer_id' => $customerId,
                        'payment_profile' => [
                            'id' => $customerId,
                        ],
                        'payment_collection_method' => 'automatic',
                        'subscription_ids' => [$subscriptionId],
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->subscriptionGroup()->create([
            'subscription_id' => $subscriptionId,
            'member_ids' => [],
        ]);

        $this->assertEquals($subscriptionId, $response['subscription_ids'][0]);
        $this->assertEquals($customerId, $response['customer_id']);
    }

    public function testSubscriptionGroupServiceListSuccess()
    {
        $groupId1 = 'grp_'.Str::random();
        $groupId2 = 'grp_'.Str::random();
        $customerId1 = random_int(10000000, 99999999);
        $customerId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push(['subscription_groups' => [
                    [
                        'uid' => $groupId1,
                        'customer_id' => $customerId1,
                    ],
                    [
                        'uid' => $groupId2,
                        'customer_id' => $customerId2,
                    ],
                ],
                    'meta' => [
                        'current_page' => 1,
                        'total_count' => 1,
                    ],
                ], 200),
        ]);

        $collection = $this->getChargify()->subscriptionGroup()->list();

        $this->assertTrue($collection->contains('customer_id', '=', $customerId1));
        $this->assertTrue($collection->contains('customer_id', '=', $customerId2));
    }
}
