<?php

namespace WinLocalInc\Chjs\Tests\Feature;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use WinLocalInc\Chjs\Enums\PaymentCollectionMethod;
use WinLocalInc\Chjs\Enums\SubscriptionStatus;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Product;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;
use WinLocalInc\Chjs\Tests\Database\Models\User;
use WinLocalInc\Chjs\Tests\Database\Models\Workspace;
use WinLocalInc\Chjs\Tests\TestCase;
use WinLocalInc\Chjs\Webhook\Handlers\ComponentPriceChange;
use WinLocalInc\Chjs\Webhook\Handlers\SubscriptionEvents;
use WinLocalInc\Chjs\Webhook\Handlers\SubscriptionPaymentUpdate;

class ChargifyWebhookEventsTest extends TestCase
{
    public function testChargifyWebhookEventsSubscriptionEvent()
    {
        $workspace = Workspace::factory()->create();

        $user = User::factory()
            ->set(
                'chargify_id',
                Uuid::uuid4()->toString()
            )
            ->set(
                'workspace_id',
                $workspace->workspace_id
            )
            ->create();

        $product = Product::factory()->count(1)->has(
            ProductPrice::factory()->count(1),
            'productPrices'
        )->create()
            ->first();

        $productPrice = $product->productPrices()->first();

        $component = Component::factory()->count(1)->has(
            ComponentPrice::factory()->count(1),
            'price'
        )->create()
            ->first();

        $componentPrice = $component->price()->first();

        $subscription = Subscription::factory()
            ->set(
                'user_id',
                $user->user_id
            )
            ->set(
                'workspace_id',
                $workspace->workspace_id
            )
            ->set(
                'product_id',
                $product->product_id
            )
            ->set(
                'product_handle',
                $product->product_handle
            )
            ->set(
                'product_price_handle',
                $productPrice->product_price_handle
            )
            ->create();

        $subscriptionComponent = SubscriptionComponent::factory()
            ->set(
                'subscription_id',
                $subscription->subscription_id
            )
            ->set(
                'component_id',
                $component->component_id
            )
            ->set(
                'component_handle',
                $component->component_handle
            )
            ->set(
                'component_price_handle',
                $componentPrice->component_price_handle
            )
            ->set(
                'component_price_id',
                $componentPrice->component_price_id
            )
            ->create();

        $nextAssessmentAt = Date::now()->toDateTimeString();
        $state = SubscriptionStatus::OnHold->value;
        $paymentMethod = PaymentCollectionMethod::Remittance->value;

        SubscriptionEvents::dispatch(
            random_int(1000000, 9999999),
            WebhookEvents::RenewalSuccess->value,
            [
                'subscription' => [
                    'id' => $subscription->subscription_id,
                    'state' => $state,
                    'payment_collection_method' => $paymentMethod,
                    'reference' => $workspace->workspace_id,
                    'trial_ended_at' => null,
                    'balance_in_cents' => '0',
                    'total_revenue_in_cents' => '10000',
                    'product_price_in_cents' => '10000',
                    'current_period_ends_at' => '2023-08-05 08:06:32 -0400',
                    'created_at' => '2023-08-05 08:06:32 -0400',
                    'updated_at' => '2023-08-05 08:06:32 -0400',
                    'next_assessment_at' => $nextAssessmentAt,
                    'scheduled_cancellation_at' => null,
                    'product_price_point_id' => $productPrice->product_price_id,
                    'product_price_point_type' => 'default',
                    'customer' => [
                        'id' => $user->chargify_id,
                        'email' => $user->email,
                        'reference' => $user->user_id,
                        'created_at' => '2023-08-05 08:06:32 -0400',
                        'updated_at' => '2023-08-05 08:06:32 -0400',
                    ],
                    'product' => [
                        'id' => $product->product_id,
                        'name' => Str::random(),
                        'description' => Str::random(),
                        'handle' => $product->product_handle,
                        'price_in_cents' => '10000',
                        'interval' => '1',
                        'interval_unit' => 'month',
                        'trial_price_in_cents' => null,
                        'trial_interval' => null,
                        'trial_interval_unit' => null,
                        'archived_at' => null,
                        'require_credit_card' => 'true',
                        'default_product_price_point_id' => $productPrice->product_price_id,
                        'product_price_point_id' => $productPrice->product_price_id,
                        'product_price_point_name' => Str::random(),
                        'product_price_point_handle' => $productPrice->product_price_handle,
                        'created_at' => '2023-08-05 08:06:32 -0400',
                        'updated_at' => '2023-08-05 08:06:32 -0400',
                        'product_family' => [
                            'id' => Str::random(),
                            'name' => Str::random(),
                            'description' => Str::random(),
                            'handle' => Str::random(),
                            'created_at' => '2023-08-05 08:06:32 -0400',
                            'updated_at' => '2023-08-05 08:06:32 -0400',
                        ],
                    ],
                    'credit_card' => [
                        'id' => Str::random(),
                        'masked_card_number' => 'XXXX-XXXX-XXXX-1111',
                        'customer_id' => $user->chargify_id,
                    ],
                ],
            ],
        );

        $this->assertDatabaseHas('chjs_subscriptions', [
            'subscription_id' => $subscription->subscription_id,
            'user_id' => $user->user_id,
            'status' => $state,
            'payment_collection_method' => $paymentMethod,
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

        SubscriptionPaymentUpdate::dispatch(
            random_int(1000000, 9999999),
            WebhookEvents::PaymentSuccess->value,
            [
                'subscription' => [
                    'id' => $subscriptionId,
                    'customer' => [
                        'id' => $customerId,
                    ],
                    'payment_collection_method' => PaymentCollectionMethod::Remittance->value,
                ],
            ]
        );

        Http::assertSentCount(3);
    }

    public function testChargifyWebhookComponentPriceChangeEvent()
    {
        $workspace = Workspace::factory()->create();

        $user = User::factory()
            ->set(
                'chargify_id',
                Uuid::uuid4()->toString()
            )
            ->set(
                'workspace_id',
                $workspace->workspace_id
            )
            ->create();

        $product = Product::factory()->count(1)->has(
            ProductPrice::factory()->count(1),
            'productPrices'
        )->create()
            ->first();

        $productPrice = $product->productPrices()->first();

        $component = Component::factory()->count(1)->has(
            ComponentPrice::factory()->count(1),
            'price'
        )->create()
            ->first();

        $componentPrice = $component->price()->first();

        $subscription = Subscription::factory()
            ->set(
                'user_id',
                $user->user_id
            )
            ->set(
                'workspace_id',
                $workspace->workspace_id
            )
            ->set(
                'product_id',
                $product->product_id
            )
            ->set(
                'product_handle',
                $product->product_handle
            )
            ->set(
                'product_price_handle',
                $productPrice->product_price_handle
            )
            ->create();

        $subscriptionComponent = SubscriptionComponent::factory()
            ->set(
                'subscription_id',
                $subscription->subscription_id
            )
            ->set(
                'component_id',
                $component->component_id
            )
            ->set(
                'component_handle',
                $component->component_handle
            )
            ->set(
                'component_price_handle',
                $componentPrice->component_price_handle
            )
            ->set(
                'component_price_id',
                $componentPrice->component_price_id
            )
            ->create();

        $quantity = 15;
        $unitPrice = '2.0';
        $finalPrice = $quantity * (int) number_format($unitPrice * 100, '0', '', '');

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'component' => [
                        'component_id' => $component->component_id,
                        'subscription_id' => $subscription->subscription_id,
                        'allocated_quantity' => $quantity,
                        'pricing_scheme' => 'per_unit',
                        'name' => 'Users',
                        'kind' => 'quantity_based_component',
                    ],
                ], 200)
                ->push([
                    'price_points' => [
                        [
                            'price_point' => [
                                'id' => $componentPrice->component_price_id,
                                'name' => 'Auto-created',
                                'type' => 'default',
                                'component_id' => $component->component_id,
                                'handle' => 'auto-created',
                                'prices' => [
                                    [
                                        'id' => 1,
                                        'component_id' => $component->component_id,
                                        'starting_quantity' => 0,
                                        'ending_quantity' => null,
                                        'unit_price' => $unitPrice,
                                        'price_point_id' => 1,
                                        'formatted_unit_price' => '$1.00',
                                        'segment_id' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200),
        ]);

        ComponentPriceChange::dispatch(
            random_int(1000000, 9999999),
            WebhookEvents::ItemPricePointChanged->value,
            [
                'item_id' => $component->component_id,
                'event_id' => random_int(1000000, 9999999),
                'item_name' => Str::random(),
                'item_type' => 'QuantityBasedComponent',
                'item_handle' => $component->component_handle,
                'subscription_id' => $subscription->subscription_id,
                'current_price_point' => [
                    'id' => $componentPrice->component_price_id,
                    'name' => 'Custom Pricing',
                    'handle' => 'uuid:f0eda2d0-4a28-013c-9351-0a0a1fe5f75b',
                ],
                'previous_price_point' => [
                    'id' => $componentPrice->component_price_id,
                    'name' => 'Custom Pricing',
                    'handle' => 'uuid:da84f840-4a28-013c-934f-0a0a1fe5f75b',
                ],
            ]
        );

        $this->assertDatabaseHas('chjs_subscription_components', [
            'subscription_id' => $subscription->subscription_id,
            'component_id' => $component->component_id,
            'subscription_component_quantity' => $quantity,
            'subscription_component_price' => $finalPrice,
        ]);
    }
}