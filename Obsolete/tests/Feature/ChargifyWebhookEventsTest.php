<?php

namespace Tests\Feature;

use App\Models\Chargify\ChargifyCustomer;
use App\Models\Chargify\ChargifyEvent;
use App\Models\Chargify\ChargifyProduct;
use App\Models\Chargify\ChargifyProductFamily;
use App\Models\Chargify\ChargifyProductPricePoint;
use App\Models\Chargify\ChargifySubscription;
use App\Models\User;
use App\Models\Workspace\Workspace;
use App\Services\Chargify\Enums\SubscriptionPaymentCollectionMethod;
use App\Services\Chargify\Enums\WebhookEvents;
use App\Services\Chargify\WebhookHandlers\SubscriptionEvents;
use App\Services\Chargify\WebhookHandlers\SubscriptionPaymentUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifyWebhookEventsTest extends TestCase
{
    use RefreshDatabase;

    public function testChargifyWebhookEventsSubscriptionEvent()
    {
        $parentUser = User::factory()->has(ChargifyCustomer::factory()->count(1), 'chargifyCustomer')->create();
        $parentCustomerId = $parentUser->chargifyCustomer->id;
        $user = User::factory()->create();

        Workspace::factory()->user($user->user_id, true)->create();

        $productFamily = ChargifyProductFamily::factory()
            ->has(ChargifyProduct::factory()->count(1)->has(
                ChargifyProductPricePoint::factory()->count(1)->has(
                    ChargifySubscription::factory()->count(1),
                    'subscriptions'
                ),
                'productPricePoints'
            ), 'products')
            ->create();

        $product = $productFamily->products()->first();

        $productPricePoint = $product->productPricePoints()->first();

        $parentSubscription = $productPricePoint->subscriptions()->first();

        $parentSubscription->user_id = $parentUser->user_id;
        $parentSubscription->workspace_id = $parentUser->workspace_id;
        $parentSubscription->save();

        $newSubscriptionId = random_int(1000000, 9999999);
        $newCustomerId = random_int(1000000, 9999999);
        $newProductFamilyId = random_int(1000000, 9999999);
        $newProductId = random_int(1000000, 9999999);
        $newProductPricePointId = random_int(1000000, 9999999);
        $newPaymentProfileId = random_int(1000000, 9999999);
        $groupId = 'grp_'.Str::random();

        $chargifyEvent = ChargifyEvent::create([
            'id' => random_int(1000000, 9999999),
            'event_name' => WebhookEvents::RenewalSuccess->value,
            'payload' => [
                'subscription' => [
                    'id' => $newSubscriptionId,
                    'state' => 'active',
                    'trial_ended_at' => null,
                    'balance_in_cents' => '0',
                    'total_revenue_in_cents' => '10000',
                    'product_price_in_cents' => '10000',
                    'current_period_ends_at' => '2023-08-05 08:06:32 -0400',
                    'created_at' => '2023-08-05 08:06:32 -0400',
                    'updated_at' => '2023-08-05 08:06:32 -0400',
                    'payer_id' => $parentCustomerId,
                    'product_price_point_id' => $newProductPricePointId,
                    'product_price_point_type' => 'default',
                    'customer' => [
                        'id' => $newCustomerId,
                        'email' => $user->email,
                        'parent_id' => $parentCustomerId,
                        'created_at' => '2023-08-05 08:06:32 -0400',
                        'updated_at' => '2023-08-05 08:06:32 -0400',
                    ],
                    'product' => [
                        'id' => $newProductId,
                        'name' => Str::random(),
                        'description' => Str::random(),
                        'handle' => Str::random(),
                        'price_in_cents' => '10000',
                        'interval' => '1',
                        'interval_unit' => 'month',
                        'trial_price_in_cents' => null,
                        'trial_interval' => null,
                        'trial_interval_unit' => null,
                        'archived_at' => null,
                        'require_credit_card' => 'true',
                        'default_product_price_point_id' => $newProductPricePointId,
                        'product_price_point_id' => $newProductPricePointId,
                        'product_price_point_name' => Str::random(),
                        'created_at' => '2023-08-05 08:06:32 -0400',
                        'updated_at' => '2023-08-05 08:06:32 -0400',
                        'product_family' => [
                            'id' => $newProductFamilyId,
                            'name' => Str::random(),
                            'description' => Str::random(),
                            'handle' => Str::random(),
                            'created_at' => '2023-08-05 08:06:32 -0400',
                            'updated_at' => '2023-08-05 08:06:32 -0400',
                        ],
                    ],
                    'credit_card' => [
                        'id' => $newPaymentProfileId,
                        'masked_card_number' => 'XXXX-XXXX-XXXX-1111',
                        'customer_id' => $newCustomerId,
                    ],
                    'group' => [
                        'uid' => $groupId,
                        'primary_subscription_id' => $parentSubscription->id,
                        'primary' => 'false',
                    ],
                ],
            ],
        ]);

        SubscriptionEvents::dispatch($chargifyEvent->id);

        $this->assertDatabaseHas('chargify_customers', [
            'id' => $newCustomerId,
            'user_id' => $user->user_id,
        ]);

        $this->assertDatabaseHas('chargify_payment_profiles', [
            'id' => $newPaymentProfileId,
            'chargify_customer_id' => $newCustomerId,
        ]);

        $this->assertDatabaseHas('chargify_product_families', [
            'id' => $newProductFamilyId,
        ]);

        $this->assertDatabaseHas('chargify_products', [
            'id' => $newProductId,
            'chargify_product_family_id' => $newProductFamilyId,
        ]);

        $this->assertDatabaseHas('chargify_product_price_points', [
            'id' => $newProductPricePointId,
            'chargify_product_id' => $newProductId,
        ]);

        $this->assertDatabaseHas('chargify_subscriptions', [
            'id' => $newSubscriptionId,
            'user_id' => $user->user_id,
            'chargify_product_price_point_id' => $newProductPricePointId,
            'chargify_subscription_group_id' => $groupId,
        ]);

        $this->assertDatabaseHas('chargify_subscription_groups', [
            'id' => $groupId,
            'chargify_customer_id' => $parentCustomerId,
            'primary_subscription_id' => $parentSubscription->id,
        ]);
    }

    public function testChargifyWebhookSubscriptionPaymentUpdateEvent()
    {
        $subscriptionId = random_int(1000000, 9999999);
        $customerId = random_int(1000000, 9999999);
        $paymentProfileId = random_int(1000000, 9999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'payment_profile' => [
                        'id' => $paymentProfileId,
                        'customer_id' => $customerId,
                    ],
                ]], 200)
                ->push([
                    'payment_profile' => [
                        'id' => $paymentProfileId,
                        'customer_id' => $customerId,
                    ],
                ], 200)
                ->push([
                    'subscription' => [
                        'id' => $subscriptionId,
                    ],
                ], 200),
        ]);

        $chargifyEvent = ChargifyEvent::create([
            'id' => random_int(1000000, 9999999),
            'event_name' => WebhookEvents::PaymentSuccess->value,
            'payload' => [
                'subscription' => [
                    'id' => $subscriptionId,
                    'customer' => [
                        'id' => $customerId,
                    ],
                    'payment_collection_method' => SubscriptionPaymentCollectionMethod::Remittance->value,
                ],
            ],
        ]);

        SubscriptionPaymentUpdate::dispatch($chargifyEvent->id);

        Http::assertSentCount(3);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('ApplianceSeeder');
        $this->seed('RoleSeeder');
        $this->seed('PlanProductPriceSeeder');
    }
}
