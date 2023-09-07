<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;

class ChargifyInsightServiceTest extends TestCase
{
    public function testInsightServiceListMrrPerSubscriptionSuccess()
    {
        $subscriptionId1 = random_int(10000000, 99999999);
        $subscriptionId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push(['subscriptions_mrr' => [[
                    'subscription_id' => $subscriptionId1,
                    'mrr_amount_in_cents' => 10000,
                    'breakouts' => [
                        'plan_amount_in_cents' => 10000,
                        'usage_amount_in_cents' => 0,
                    ],
                ], [
                    'subscription_id' => $subscriptionId2,
                    'mrr_amount_in_cents' => 10000,
                    'breakouts' => [
                        'plan_amount_in_cents' => 10000,
                        'usage_amount_in_cents' => 0,
                    ],
                ],
                ]], 200),
        ]);

        $collection = $this->getChargify()->insight()->listMrrPerSubscription([
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertTrue($collection->contains('subscription_id', '=', $subscriptionId1));
        $this->assertTrue($collection->contains('subscription_id', '=', $subscriptionId2));
    }
}
