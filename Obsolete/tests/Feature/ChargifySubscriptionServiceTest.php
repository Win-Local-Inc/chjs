<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifySubscriptionServiceTest extends TestCase
{
    public function testSubscriptionServiceCreateSuccess()
    {
        $productId = random_int(10000000, 99999999);
        $subscriptionId = random_int(10000000, 99999999);
        $customerId = random_int(10000000, 99999999);
        $chargifyToken = 'tok_'.Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription' => [
                        'id' => $subscriptionId,
                        'state' => 'active',
                        'product' => ['id' => $productId],
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->subscription()->create([
            'product_id' => $productId,
            'customer_id' => $customerId,
            'credit_card_attributes' => [
                'chargify_token' => $chargifyToken,
                'payment_type' => 'credit_card',
            ],
        ]);

        $this->assertEquals($subscriptionId, $response['id']);
        $this->assertEquals($productId, $response['product']['id']);
    }

    public function testCouponServiceUpdateSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);
        $productId = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription' => [
                        'next_product_id' => $productId,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->subscription()->update($subscriptionId, [
            'next_product_id' => $productId,
        ]);

        $this->assertEquals($productId, $response['next_product_id']);
    }

    public function testSubscriptionServiceGetByIdSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription' => [
                        'id' => $subscriptionId,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->subscription()->getSubscriptionById($subscriptionId);

        $this->assertEquals($subscriptionId, $response['id']);
    }

    public function testSubscriptionServiceAddCouponsSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);
        $codes = [Str::upper(Str::random()), Str::upper(Str::random())];

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription' => [
                        'id' => $subscriptionId,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->subscription()->addCouponsToSubscription($subscriptionId, $codes);

        $this->assertEquals($subscriptionId, $response['id']);
    }

    public function testSubscriptionServiceDelteCouponSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);
        $code = Str::upper(Str::random());

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([], 204),
        ]);

        $this->getChargify()->subscription()->removeCouponFromSubscription($subscriptionId, $code);

        Http::assertSentCount(1);
    }

    public function testSubscriptionServiceListSubscriptionsSuccess()
    {
        $subscriptionId1 = random_int(10000000, 99999999);
        $subscriptionId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'subscription' => [
                        'id' => $subscriptionId1,
                    ],
                ], [
                    'subscription' => [
                        'id' => $subscriptionId2,
                    ],
                ],
                ], 200),
        ]);

        $collection = $this->getChargify()->subscription()->listSubscriptions([
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertTrue($collection->contains('id', '=', $subscriptionId1));
        $this->assertTrue($collection->contains('id', '=', $subscriptionId2));
    }
}
