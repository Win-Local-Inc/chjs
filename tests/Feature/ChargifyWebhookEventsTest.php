<?php

namespace WinLocalInc\Chjs\Tests\Feature;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Database\Seeders\ComponentSeeder;
use WinLocalInc\Chjs\Database\Seeders\ProductSeeder;
use WinLocalInc\Chjs\Enums\MainComponent;
use WinLocalInc\Chjs\Enums\PaymentCollectionMethod;
use WinLocalInc\Chjs\Enums\Product as ProductEnum;
use WinLocalInc\Chjs\Enums\ProductPricing;
use WinLocalInc\Chjs\Enums\ShareCardProPricing;
use WinLocalInc\Chjs\Enums\SubscriptionStatus;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Metafield;
use WinLocalInc\Chjs\Models\Product;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;
use WinLocalInc\Chjs\Tests\Database\Models\User;
use WinLocalInc\Chjs\Tests\Database\Models\Workspace;
use WinLocalInc\Chjs\Tests\TestCase;
use WinLocalInc\Chjs\Webhook\Handlers\ComponentPriceChange;
use WinLocalInc\Chjs\Webhook\Handlers\MetafieldUpdate;
use WinLocalInc\Chjs\Webhook\Handlers\SubscriptionEvents;
use WinLocalInc\Chjs\Webhook\Handlers\SubscriptionPaymentUpdate;

class ChargifyWebhookEventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
        $this->seed(ComponentSeeder::class);
    }

    public function testChargifyWebhookEventsSubscriptionEvent()
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()
            ->workspace($workspace)
            ->withChargifyId()
            ->create();

        $product = Product::where('product_handle', ProductEnum::SOLO->value)->first();
        $productPrice = ProductPrice::where('product_price_handle', ProductPricing::SOLO_MONTH->value)->first();

        $component = Component::where('component_handle', ShareCardProPricing::MONTH->value)->first();
        $componentPrice = ComponentPrice::where('component_price_handle', ShareCardProPricing::MONTH->value)->first();

        $subscription = Subscription::factory()
            ->workspace($workspace)
            ->user($user)
            ->productPrice($productPrice)
            ->create();

        SubscriptionComponent::factory()
            ->subscription($subscription)
            ->component($component)
            ->componentPrice($componentPrice)
            ->create();

        $nextAssessmentAt = Date::now()->toDateTimeString();
        $state = SubscriptionStatus::OnHold->value;
        $paymentMethod = PaymentCollectionMethod::Remittance->value;

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'component' => [
                        'component_id' => $component->component_id,
                        'component_handle' => $component->component_handle,
                        'price_point_handle' => $componentPrice->component_price_handle,
                        'price_point_id' => $componentPrice->component_price_id,
                        'subscription_id' => $subscription->subscription_id,
                        'allocated_quantity' => 1,
                        'pricing_scheme' => 'per_unit',
                        'name' => 'Users',
                        'kind' => 'quantity_based_component',
                        'created_at' => '2023-08-05 08:06:32 -0400',
                        'updated_at' => '2023-08-05 08:06:32 -0400',
                    ],
                ]], 200)
                ->push([
                    'price_points' => [
                        [
                            'id' => $componentPrice->component_price_id,
                            'component_id' => $component->component_id,
                            'name' => 'Auto-created',
                            'type' => 'default',
                            'handle' => 'auto-created',
                            'prices' => [
                                [
                                    'id' => 1,
                                    'component_id' => $component->component_id,
                                    'starting_quantity' => 0,
                                    'ending_quantity' => null,
                                    'unit_price' => '1.00',
                                    'price_point_id' => 1,
                                    'formatted_unit_price' => '$1.00',
                                    'segment_id' => null,
                                ],
                            ],
                        ],
                    ],
                ], 200),
        ]);

        SubscriptionEvents::dispatch(
            random_int(1000000, 9999999),
            WebhookEvents::SubscriptionStateChange->value,
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
            'component' => MainComponent::SHARE_CARD_PRO->name,
            'component_handle' => ShareCardProPricing::MONTH->value,
        ]);

        $this->assertDatabaseHas('chjs_subscription_components', [
            'subscription_id' => $subscription->subscription_id,
            'is_main_component' => 1,
            'component_handle' => ShareCardProPricing::MONTH->value,
        ]);

        $this->assertDatabaseHas('chjs_subscription_histories', [
            'subscription_id' => $subscription->subscription_id,
            'workspace_id' => $workspace->workspace_id,
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
            ->workspace($workspace)
            ->withChargifyId()
            ->create();

        $product = Product::where('product_handle', ProductEnum::PROMO->value)->first();
        $productPrice = ProductPrice::where('product_price_handle', ProductPricing::SOLO_MONTH->value)->first();

        $component = Component::where('component_handle', ShareCardProPricing::MONTH->value)->first();
        $componentPrice = ComponentPrice::where('component_price_handle', ShareCardProPricing::MONTH->value)->first();

        $subscription = Subscription::factory()
            ->user($user)
            ->workspace($workspace)
            ->productPrice($productPrice)
            ->create();

        SubscriptionComponent::factory()
            ->subscription($subscription)
            ->component($component)
            ->componentPrice($componentPrice)
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

        $this->assertDatabaseHas('chjs_subscription_histories', [
            'subscription_id' => $subscription->subscription_id,
            'workspace_id' => $workspace->workspace_id,
        ]);
    }

    public function testChargifyWebhookEventsMetafieldCreateEvent()
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()
            ->workspace($workspace)
            ->withChargifyId()
            ->create();

        $productPrice = ProductPrice::where('product_price_handle', ProductPricing::SOLO_MONTH->value)->first();

        $subscription = Subscription::factory()
            ->workspace($workspace)
            ->user($user)
            ->productPrice($productPrice)
            ->create();

        $metafield = Metafield::factory()->create();

        MetafieldUpdate::dispatch(
            random_int(1000000, 9999999),
            WebhookEvents::CustomFieldValueChange->value,
            [
                'site' => [
                    'id' => random_int(1000, 9999),
                    'subdomain' => 'win-local',
                ],
                'metafield' => [
                    'event_type' => 'created',
                    'metafield_name' => $metafield->key,
                    'metafield_id' => $metafield->id,
                    'old_value' => 'nil',
                    'new_value' => $metafield->value,
                    'resource_type' => 'Subscription',
                    'resource_id' => $subscription->subscription_id,
                ],
                'event_id' => random_int(1000, 9999),
            ]
        );

        $this->assertDatabaseHas('chjs_metafield_subscription', [
            'metafield_id' => $metafield->id,
            'workspace_id' => $workspace->workspace_id,
        ]);

    }

    public function testChargifyWebhookEventsMetafieldUpdateEvent()
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()
            ->workspace($workspace)
            ->withChargifyId()
            ->create();

        $productPrice = ProductPrice::where('product_price_handle', ProductPricing::SOLO_MONTH->value)->first();

        $subscription = Subscription::factory()
            ->workspace($workspace)
            ->user($user)
            ->productPrice($productPrice)
            ->create();

        $key = Str::random();

        $metafieldNew = Metafield::factory()->state(['key' => $key])
            ->afterCreating(function (Metafield $meta) {
                $meta->sha1_hash = sha1(mb_strtolower($meta->key.$meta->value));
                $meta->save();
            })
            ->create();

        $metafieldOld = Metafield::factory()->state(['key' => $key])
            ->afterCreating(function (Metafield $meta) {
                $meta->sha1_hash = sha1(mb_strtolower($meta->key.$meta->value));
                $meta->save();
            })
            ->create();

        $subscription->metafields()->attach($metafieldOld);

        MetafieldUpdate::dispatch(
            random_int(1000000, 9999999),
            WebhookEvents::CustomFieldValueChange->value,
            [
                'site' => [
                    'id' => random_int(1000, 9999),
                    'subdomain' => 'win-local',
                ],
                'metafield' => [
                    'event_type' => 'updated',
                    'metafield_name' => $key,
                    'metafield_id' => $metafieldNew->id,
                    'old_value' => $metafieldOld->value,
                    'new_value' => $metafieldNew->value,
                    'resource_type' => 'Subscription',
                    'resource_id' => $subscription->subscription_id,
                ],
                'event_id' => random_int(1000, 9999),
            ]
        );

        $this->assertDatabaseMissing('chjs_metafield_subscription', [
            'metafield_id' => $metafieldOld->id,
            'workspace_id' => $workspace->workspace_id,
        ]);

        $this->assertDatabaseHas('chjs_metafield_subscription', [
            'metafield_id' => $metafieldNew->id,
            'workspace_id' => $workspace->workspace_id,
        ]);

    }

    public function testChargifyWebhookEventsMetafieldDeleteEvent()
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()
            ->workspace($workspace)
            ->withChargifyId()
            ->create();

        $productPrice = ProductPrice::where('product_price_handle', ProductPricing::SOLO_MONTH->value)->first();

        $subscription = Subscription::factory()
            ->workspace($workspace)
            ->user($user)
            ->productPrice($productPrice)
            ->create();

        $metafield = Metafield::factory()->create();

        $subscription->metafields()->attach($metafield);

        MetafieldUpdate::dispatch(
            random_int(1000000, 9999999),
            WebhookEvents::CustomFieldValueChange->value,
            [
                'site' => [
                    'id' => random_int(1000, 9999),
                    'subdomain' => 'win-local',
                ],
                'metafield' => [
                    'event_type' => 'deleted',
                    'metafield_name' => $metafield->key,
                    'metafield_id' => $metafield->id,
                    'old_value' => $metafield->value,
                    'new_value' => 'nil',
                    'resource_type' => 'Subscription',
                    'resource_id' => $subscription->subscription_id,
                ],
                'event_id' => random_int(1000, 9999),
            ]
        );

        $this->assertDatabaseMissing('chjs_metafield_subscription', [
            'metafield_id' => $metafield->id,
            'workspace_id' => $workspace->workspace_id,
        ]);

    }
}
