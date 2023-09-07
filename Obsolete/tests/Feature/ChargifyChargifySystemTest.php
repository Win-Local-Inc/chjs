<?php

namespace Tests\Feature;

use App\Models\Chargify\ChargifyComponent;
use App\Models\Chargify\ChargifyComponentPricePoint;
use App\Models\Chargify\ChargifyCoupon;
use App\Models\Chargify\ChargifyCustomer;
use App\Models\Chargify\ChargifyPaymentProfile;
use App\Models\Chargify\ChargifyProduct;
use App\Models\Chargify\ChargifyProductFamily;
use App\Models\Chargify\ChargifyProductPricePoint;
use App\Models\Chargify\ChargifySubscription;
use App\Models\Chargify\ChargifySubscriptionGroup;
use App\Models\User;
use App\Models\Workspace\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifyChargifySystemTest extends TestCase
{
    use RefreshDatabase;

    public function testChargifySystemCreateUserFromTokenSuccess()
    {
        $customerId = random_int(1000000, 9999999);
        $user = User::factory()->create();

        $paymentProfileId = random_int(1000000, 9999999);

        $parentUser = User::factory()->has(
            ChargifyCustomer::factory()->count(1),
            'chargifyCustomer'
        )->create();
        $parentCustomerId = $parentUser->chargifyCustomer->id;

        $chargifyToken = 'tok_'.Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'payment_profile' => [
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                        'billing_address' => 'Summer',
                        'billing_address_2' => 'Street',
                        'billing_city' => 'New York',
                        'billing_country' => 'US',
                        'billing_state' => 'NY',
                        'billing_zip' => '10001',
                    ],
                ], 200)
                ->push([
                    'customer' => [
                        'id' => $customerId,
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                        'email' => $user->email,
                        'address' => 'string',
                        'address_2' => 'string',
                        'city' => 'string',
                        'state' => 'string',
                        'state_name' => 'string',
                        'zip' => 'string',
                        'country' => 'string',
                        'country_name' => 'string',
                        'parent_id' => null,
                    ],
                ], 200)
                ->push([
                    'payment_profile' => [
                        'id' => $paymentProfileId,
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                        'masked_card_number' => 'XXXX-XXXX-XXXX-1111',
                        'customer_id' => $customerId,
                        'payment_type' => 'credit_card',
                    ],
                ], 200),
        ]);

        $responseCustomerId = $this->getChargifySystem()
            ->upsertCustomerWithPaymentProfileFromToken($user, $chargifyToken, $parentCustomerId);

        $this->assertDatabaseHas('chargify_customers', [
            'id' => $responseCustomerId,
            'user_id' => $user->user_id,
        ]);

        $this->assertDatabaseHas('chargify_payment_profiles', [
            'id' => $paymentProfileId,
            'chargify_customer_id' => $responseCustomerId,
        ]);

        $user->refresh();
        $parentUser->refresh();

        $this->assertEquals(
            $parentUser->chargifyCustomer->id,
            $user->chargifyCustomer->parent_id
        );
    }

    public function testChargifySystemCreatePaymentProfileFromTokenSuccess()
    {
        $customerId = random_int(1000000, 9999999);
        $user = User::factory()->create();

        $paymentProfileId = random_int(1000000, 9999999);

        $chargifyToken = 'tok_'.Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'payment_profile' => [
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                        'billing_address' => 'Summer',
                        'billing_address_2' => 'Street',
                        'billing_city' => 'New York',
                        'billing_country' => 'US',
                        'billing_state' => 'NY',
                        'billing_zip' => '10001',
                    ],
                ], 200)
                ->push([
                    'customer' => [
                        'id' => $customerId,
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                        'email' => $user->email,
                        'address' => 'string',
                        'address_2' => 'string',
                        'city' => 'string',
                        'state' => 'string',
                        'state_name' => 'string',
                        'zip' => 'string',
                        'country' => 'string',
                        'country_name' => 'string',
                        'parent_id' => null,
                    ],
                ], 200)
                ->push([
                    'payment_profile' => [
                        'id' => $paymentProfileId,
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                        'masked_card_number' => 'XXXX-XXXX-XXXX-1111',
                        'customer_id' => $customerId,
                        'payment_type' => 'credit_card',
                    ],
                ], 200),
        ]);

        $user->createPaymnetProfile($chargifyToken);
        $user->refresh();
        $this->assertDatabaseHas('chargify_customers', [
            'id' => $user->chargifyCustomer->id,
            'user_id' => $user->user_id,
        ]);

        $this->assertDatabaseHas('chargify_payment_profiles', [
            'id' => $paymentProfileId,
            'chargify_customer_id' => $user->chargifyCustomer->id,
        ]);
    }

    public function testChargifySystemAttachParentSuccess()
    {
        $user = User::factory()->has(
            ChargifyCustomer::factory()->count(1),
            'chargifyCustomer'
        )->create();

        $parentUser = User::factory()->has(
            ChargifyCustomer::factory()->count(1),
            'chargifyCustomer'
        )->create();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'customer' => [
                        'id' => $user->chargifyCustomer->id,
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                        'email' => $user->email,
                        'parent_id' => $parentUser->chargifyCustomer->id,
                    ],
                ], 200),
        ]);

        $this->getChargifySystem()
            ->attachCustomerToParent($parentUser->chargifyCustomer->id, $user->chargifyCustomer->id);

        $user->refresh();
        $parentUser->refresh();

        $this->assertDatabaseHas('chargify_customers', [
            'id' => $user->chargifyCustomer->id,
            'user_id' => $user->user_id,
            'parent_id' => $parentUser->chargifyCustomer->id,
        ]);

        $this->assertEquals(
            $parentUser->chargifyCustomer->id,
            $user->chargifyCustomer->parent_id
        );
    }

    public function testChargifySystemDetachParentSuccess()
    {
        $parentUser = User::factory()->has(
            ChargifyCustomer::factory()->count(1),
            'chargifyCustomer'
        )->create();

        $user = User::factory()->has(
            ChargifyCustomer::factory()->set(
                'parent_id',
                $parentUser->chargifyCustomer->id
            )->count(1),
            'chargifyCustomer'
        )->create();

        $this->assertDatabaseHas('chargify_customers', [
            'id' => $user->chargifyCustomer->id,
            'user_id' => $user->user_id,
            'parent_id' => $parentUser->chargifyCustomer->id,
        ]);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'customer' => [
                        'id' => $user->chargifyCustomer->id,
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                        'email' => $user->email,
                        'parent_id' => null,
                    ],
                ], 200),
        ]);

        $this->getChargifySystem()
            ->detachCustomerFromParent($user->chargifyCustomer->id);

        $user->refresh();
        $parentUser->refresh();

        $this->assertDatabaseHas('chargify_customers', [
            'id' => $user->chargifyCustomer->id,
            'user_id' => $user->user_id,
            'parent_id' => null,
        ]);

        $this->assertEquals(
            null,
            $user->chargifyCustomer->parent_id
        );
    }

    public function testChargifySystemCreateSubscriptionSuccess()
    {
        $parentUser = User::factory()->has(ChargifyCustomer::factory()->count(1), 'chargifyCustomer')->create();
        $parentCustomerId = $parentUser->chargifyCustomer->id;

        $user = User::factory()->has(
            ChargifyCustomer::factory()->set(
                'parent_id',
                $parentCustomerId
            )
                ->has(ChargifyPaymentProfile::factory()->count(1), 'paymentProfiles')
                ->count(1),
            'chargifyCustomer'
        )
            ->create();
        $customerId = $user->chargifyCustomer->id;

        $workspace = Workspace::factory()->user($user->user_id, true)->create();

        $productFamily = ChargifyProductFamily::factory()
            ->has(ChargifyProduct::factory()->count(1)->has(
                ChargifyProductPricePoint::factory()->count(1)->has(
                    ChargifySubscription::factory()->count(1),
                    'subscriptions'
                ),
                'productPricePoints'
            ), 'products')
            ->has(ChargifyComponent::factory()->has(
                ChargifyComponentPricePoint::factory()->count(1),
                'componentPricePoints'
            )->count(1), 'components')
            ->has(ChargifyCoupon::factory()->count(1), 'coupons')
            ->create();

        $product = $productFamily->products()->first();

        $productPricePoint = $product->productPricePoints()->first();

        $parentSubscription = $productPricePoint->subscriptions()->first();

        $subscriptionGroup = ChargifySubscriptionGroup::factory()
            ->set('chargify_customer_id', $parentCustomerId)
            ->set('primary_subscription_id', $parentSubscription->id)
            ->create();

        $parentSubscription->chargify_subscription_group_id = $subscriptionGroup->id;
        $parentSubscription->user_id = $parentUser->user_id;
        $parentSubscription->workspace_id = $parentUser->workspace_id;
        $parentSubscription->save();

        $component = $productFamily->components()->first();
        $componentPricePoint = $component->componentPricePoints()->first();
        $coupon = $productFamily->coupons()->first();

        $newSubscriptionId = random_int(1000, 9999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription' => [
                        'id' => $newSubscriptionId,
                        'state' => 'active',
                        'trial_ended_at' => null,
                        'balance_in_cents' => '0',
                        'current_period_ends_at' => '2023-08-01T11:13:33-04:00',
                        'next_assessment_at' => '2023-08-01T11:13:33-04:00',
                        'current_period_started_at' => '2023-08-01T11:13:33-04:00',
                        'previous_state' => 'active',
                        'total_revenue_in_cents' => '10000',
                        'product_price_in_cents' => '10000',
                        'offer_id' => null,
                        'payer_id' => $parentCustomerId,
                        'product_price_point_id' => $productPricePoint->id,
                        'currency' => 'USD',
                        'created_at' => '2023-08-01T11:13:33-04:00',
                        'updated_at' => '2023-08-01T11:13:33-04:00',
                        'customer' => [
                            'id' => $customerId,
                            'created_at' => '2023-08-01T11:13:33-04:00',
                            'updated_at' => '2023-08-01T11:13:33-04:00',
                        ],
                        'product' => [
                            'id' => $product->id,
                            'product_price_point_id' => $productPricePoint->id,
                            'created_at' => '2023-08-01T11:13:33-04:00',
                            'updated_at' => '2023-08-01T11:13:33-04:00',
                            'product_family' => [
                                'id' => $productFamily->id,
                                'created_at' => '2023-08-01T11:13:33-04:00',
                                'updated_at' => '2023-08-01T11:13:33-04:00',
                            ],
                        ],
                        'credit_card' => [
                            'id' => '52514769',
                        ],
                        'group' => [
                            'uid' => $subscriptionGroup->id,
                            'scheme' => '1',
                            'primary_subscription_id' => $parentSubscription->id,
                            'primary' => 'false',
                        ],
                    ],
                ], 200),
        ]);

        $responseId = $this->getChargifySystem()
            ->createSubscription(
                $user,
                $workspace->workspace_id,
                [
                    'product_id' => $product->id,
                    'product_price_point_id' => $productPricePoint->id,
                ],
                [
                    'user_id' => $user->id,
                    'workspace_id' => $workspace->workspace_id,
                ],
                [[
                    'component_id' => $component->id,
                    'unit_balance' => 20,
                    'price_point_id' => $componentPricePoint->id,
                ]],
                [[
                    'coupon_id' => $coupon->id,
                    'coupon_code' => $coupon->code,
                ]],
                $subscriptionGroup->id
            );

        $this->assertDatabaseHas('chargify_subscriptions', [
            'id' => $responseId,
            'user_id' => $user->user_id,
            'chargify_subscription_group_id' => $subscriptionGroup->id,
        ]);
    }

    public function testChargifySystemCreateSubscriptionGroupSuccess()
    {
        $parentUser = User::factory()->has(ChargifyCustomer::factory()->count(1), 'chargifyCustomer')->create();
        $parentCustomerId = $parentUser->chargifyCustomer->id;

        $user = User::factory()->has(
            ChargifyCustomer::factory()->set(
                'parent_id',
                $parentCustomerId
            )->count(1),
            'chargifyCustomer'
        )->create();

        Workspace::factory()->user($user->user_id, true)->create();
        Workspace::factory()->user($parentUser->user_id, true)->create();

        $productFamily = ChargifyProductFamily::factory()
            ->has(ChargifyProduct::factory()->count(1)->has(
                ChargifyProductPricePoint::factory()->count(1)->has(
                    ChargifySubscription::factory()->count(2),
                    'subscriptions'
                ),
                'productPricePoints'
            ), 'products')->create();

        $product = $productFamily->products()->first();

        $productPricePoint = $product->productPricePoints()->first();

        //===========================================================//

        $subscriptions = $productPricePoint->subscriptions()->get();

        $subscription = $subscriptions[0];
        $subscription->user_id = $user->user_id;
        $subscription->workspace_id = $user->workspace_id;
        $subscription->save();

        $parentSubscription = $subscriptions[1];
        $parentSubscription->user_id = $parentUser->user_id;
        $parentSubscription->workspace_id = $parentUser->workspace_id;
        $parentSubscription->save();

        //===========================================================//

        $groupId = 'grp_'.Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription_group' => [
                        'customer_id' => $parentCustomerId,
                        'payment_profile' => [
                            'id' => 1,
                        ],
                        'payment_collection_method' => 'automatic',
                        'subscription_ids' => [
                            $parentSubscription->id,
                            $subscription->id,
                        ],
                    ],
                ], 200)
                ->push([
                    'uid' => $groupId,
                    'scheme' => 1,
                    'customer_id' => $parentCustomerId,
                    'payment_profile_id' => 1,
                    'subscription_ids' => [
                        $parentSubscription->id,
                        $subscription->id,
                    ],
                    'primary_subscription_id' => $parentSubscription->id,
                ], 200),
        ]);

        $responseGroupId = $this->getChargifySystem()
            ->createSubscriptionGroup($parentCustomerId, $parentSubscription->id, [$subscription->id]);

        $subscription->refresh();
        $parentSubscription->refresh();

        $this->assertDatabaseHas('chargify_subscription_groups', [
            'id' => $responseGroupId,
            'chargify_customer_id' => $parentCustomerId,
            'primary_subscription_id' => $parentSubscription->id,
        ]);

        $this->assertEquals(
            $responseGroupId,
            $subscription->chargify_subscription_group_id
        );

        $this->assertEquals(
            $responseGroupId,
            $parentSubscription->chargify_subscription_group_id
        );
    }

    public function testChargifySystemAttachSubscriptionToGroupSuccess()
    {
        $parentUser = User::factory()->has(ChargifyCustomer::factory()->count(1), 'chargifyCustomer')->create();
        $parentCustomerId = $parentUser->chargifyCustomer->id;

        $user = User::factory()->has(
            ChargifyCustomer::factory()->set(
                'parent_id',
                $parentCustomerId
            )->count(1),
            'chargifyCustomer'
        )->create();

        Workspace::factory()->user($user->user_id, true)->create();
        Workspace::factory()->user($parentUser->user_id, true)->create();

        $productFamily = ChargifyProductFamily::factory()
            ->has(ChargifyProduct::factory()->count(1)->has(
                ChargifyProductPricePoint::factory()->count(1)->has(
                    ChargifySubscription::factory()->count(2),
                    'subscriptions'
                ),
                'productPricePoints'
            ), 'products')->create();

        $product = $productFamily->products()->first();

        $productPricePoint = $product->productPricePoints()->first();

        //===========================================================//

        $subscriptions = $productPricePoint->subscriptions()->get();

        $subscription = $subscriptions[0];
        $subscription->user_id = $user->user_id;
        $subscription->workspace_id = $user->workspace_id;
        $subscription->save();

        $parentSubscription = $subscriptions[1];

        $subscriptionGroup = ChargifySubscriptionGroup::factory()
            ->set('chargify_customer_id', $parentCustomerId)
            ->set('primary_subscription_id', $parentSubscription->id)
            ->create();

        $parentSubscription->chargify_subscription_group_id = $subscriptionGroup->id;
        $parentSubscription->user_id = $parentUser->user_id;
        $parentSubscription->workspace_id = $parentUser->workspace_id;
        $parentSubscription->save();

        //===========================================================//

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription_group' => [
                        'customer_id' => $parentCustomerId,
                        'payment_profile' => [
                            'id' => 1,
                        ],
                        'payment_collection_method' => 'automatic',
                        'subscription_ids' => [
                            $parentSubscription->id,
                            $subscription->id,
                        ],
                    ],
                ], 200),
        ]);

        $this->getChargifySystem()
            ->attachSubscriptionToGroup($subscriptionGroup->id, $subscription->id);

        $subscription->refresh();

        $this->assertEquals(
            $subscriptionGroup->id,
            $subscription->chargify_subscription_group_id
        );
    }

    public function testChargifySystemDetachSubscriptionFromGroupSuccess()
    {
        $parentUser = User::factory()->has(ChargifyCustomer::factory()->count(1), 'chargifyCustomer')->create();
        $parentCustomerId = $parentUser->chargifyCustomer->id;

        $user = User::factory()->has(
            ChargifyCustomer::factory()->set(
                'parent_id',
                $parentCustomerId
            )->count(1),
            'chargifyCustomer'
        )->create();

        Workspace::factory()->user($user->user_id, true)->create();
        Workspace::factory()->user($parentUser->user_id, true)->create();

        $productFamily = ChargifyProductFamily::factory()
            ->has(ChargifyProduct::factory()->count(1)->has(
                ChargifyProductPricePoint::factory()->count(1)->has(
                    ChargifySubscription::factory()->count(2),
                    'subscriptions'
                ),
                'productPricePoints'
            ), 'products')->create();

        $product = $productFamily->products()->first();

        $productPricePoint = $product->productPricePoints()->first();

        //===========================================================//

        $subscriptions = $productPricePoint->subscriptions()->get();

        $parentSubscription = $subscriptions[1];

        $subscriptionGroup = ChargifySubscriptionGroup::factory()
            ->set('chargify_customer_id', $parentCustomerId)
            ->set('primary_subscription_id', $parentSubscription->id)
            ->create();

        $parentSubscription->chargify_subscription_group_id = $subscriptionGroup->id;
        $parentSubscription->user_id = $parentUser->user_id;
        $parentSubscription->workspace_id = $parentUser->workspace_id;
        $parentSubscription->save();

        $subscription = $subscriptions[0];
        $subscription->chargify_subscription_group_id = $subscriptionGroup->id;
        $subscription->user_id = $user->user_id;
        $subscription->workspace_id = $user->workspace_id;
        $subscription->save();

        //===========================================================//

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([], 204),
        ]);

        $this->getChargifySystem()
            ->detachSubscriptionFromGroup($subscription->id);

        $subscription->refresh();

        $this->assertEquals(
            null,
            $subscription->chargify_subscription_group_id
        );
    }

    public function testChargifySystemRemoveSubscriptionGroupSuccess()
    {
        $parentUser = User::factory()->has(ChargifyCustomer::factory()->count(1), 'chargifyCustomer')->create();
        $parentCustomerId = $parentUser->chargifyCustomer->id;

        $user = User::factory()->has(
            ChargifyCustomer::factory()->set(
                'parent_id',
                $parentCustomerId
            )->count(1),
            'chargifyCustomer'
        )->create();

        Workspace::factory()->user($user->user_id, true)->create();
        Workspace::factory()->user($parentUser->user_id, true)->create();

        $productFamily = ChargifyProductFamily::factory()
            ->has(ChargifyProduct::factory()->count(1)->has(
                ChargifyProductPricePoint::factory()->count(1)->has(
                    ChargifySubscription::factory()->count(2),
                    'subscriptions'
                ),
                'productPricePoints'
            ), 'products')->create();

        $product = $productFamily->products()->first();

        $productPricePoint = $product->productPricePoints()->first();

        //===========================================================//

        $subscriptions = $productPricePoint->subscriptions()->get();

        $parentSubscription = $subscriptions[1];

        $subscriptionGroup = ChargifySubscriptionGroup::factory()
            ->set('chargify_customer_id', $parentCustomerId)
            ->set('primary_subscription_id', $parentSubscription->id)
            ->create();

        $parentSubscription->chargify_subscription_group_id = $subscriptionGroup->id;
        $parentSubscription->user_id = $parentUser->user_id;
        $parentSubscription->workspace_id = $parentUser->workspace_id;
        $parentSubscription->save();

        $subscription = $subscriptions[0];
        $subscription->chargify_subscription_group_id = $subscriptionGroup->id;
        $subscription->user_id = $user->user_id;
        $subscription->workspace_id = $user->workspace_id;
        $subscription->save();

        //===========================================================//

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'subscription_group' => [
                        'customer_id' => $parentCustomerId,
                        'payment_profile' => [
                            'id' => 1,
                        ],
                        'payment_collection_method' => 'automatic',
                        'subscription_ids' => [
                            $parentSubscription->id,
                        ],
                    ],
                ], 200)
                ->push([
                    'uid' => $subscriptionGroup->id,
                    'deleted' => true,
                ], 204),
        ]);

        $this->getChargifySystem()->removeSubscriptionGroup($subscriptionGroup->id);

        $subscription->refresh();
        $parentSubscription->refresh();

        $this->assertEquals(
            null,
            $subscription->chargify_subscription_group_id
        );

        $this->assertEquals(
            null,
            $parentSubscription->chargify_subscription_group_id
        );

        $this->assertDatabaseMissing('chargify_subscription_groups', [
            'id' => $subscriptionGroup->id,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('ApplianceSeeder');
        $this->seed('RoleSeeder');
        $this->seed('PlanProductPriceSeeder');
    }
}
