<?php

namespace WinLocalInc\Chjs\Tests\Feature;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Enums\PaymentCollectionMethod;
use WinLocalInc\Chjs\Enums\ProductPricing;
use WinLocalInc\Chjs\Enums\SubscriptionStatus;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Product;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Tests\Database\Models\User;
use WinLocalInc\Chjs\Tests\Database\Models\Workspace;
use WinLocalInc\Chjs\Tests\TestCase;

class ChargifySubscriptionBuilderTest extends TestCase
{
    public function testCreateSubscription()
    {
        $workspace = Workspace::factory()->create();
        $customerId = random_int(1000000, 9999999);

        $user = User::factory()
            ->set(
                'chargify_id',
                $customerId
            )
            ->set(
                'workspace_id',
                $workspace->workspace_id
            )
            ->create();

        $workspace->owner_id = $user->user_id;
        $workspace->save();

        $subscriptionId = random_int(1000000, 9999999);
        $paymentProfileId = random_int(1000000, 9999999);
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
        $componentQuantity = 10;

        $nextAssessmentAt = Date::now()->toDateTimeString();
        $state = SubscriptionStatus::Active->value;
        $paymentMethod = PaymentCollectionMethod::Automatic->value;
        $coupon = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([

                    'subscription' => [
                        'id' => $subscriptionId,
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

                ], 200)
                ->push([
                    [
                        'component' => [
                            'component_id' => $component->component_id,
                            'subscription_id' => $subscriptionId,
                            'allocated_quantity' => $componentQuantity,
                            'price_point_id' => $componentPrice->component_price_id,
                            'price_point_handle' => $componentPrice->component_price_handle,
                            'created_at' => '2023-01-01 10:00:00',
                            'updated_at' => '2023-01-01 10:00:00',
                            'component_handle' => $component->component_handle,
                        ],
                    ],
                ], 200)
                ->push([
                    'price_points' => [
                        [
                            'id' => $componentPrice->component_price_id,
                            'type' => 'default',
                            'component_id' => $component->component_id,
                            'handle' => $componentPrice->component_price_handle,
                            'created_at' => '2023-01-01 10:00:00',
                            'updated_at' => '2023-01-01 10:00:00',
                            'prices' => [
                                [
                                    'id' => 1,
                                    'component_id' => $component->component_id,
                                    'unit_price' => '10.0',
                                    'price_point_id' => $componentPrice->component_price_id,
                                    'formatted_unit_price' => '$1.00',
                                ],
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $workspace->newSubscription(ProductPricing::from($productPrice->product_price_handle))
            ->paymentProfile($paymentProfileId)
            ->component($componentPrice, 10)
            ->coupon($coupon)
            ->create();

        $this->assertDatabaseHas('chjs_subscriptions', [
            'subscription_id' => $subscriptionId,
            'user_id' => $user->user_id,
            'status' => $state,
            'payment_collection_method' => $paymentMethod,
        ]);

        $this->assertDatabaseHas('chjs_subscription_components', [
            'subscription_id' => $subscriptionId,
            'component_id' => $component->component_id,
            'subscription_component_quantity' => $componentQuantity,
        ]);
    }
}
