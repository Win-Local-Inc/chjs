<?php

namespace WinLocalInc\Chjs\Tests\Feature;

use Illuminate\Support\Facades\Http;
use WinLocalInc\Chjs\Enums\ProductPricing;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Product;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;
use WinLocalInc\Chjs\Tests\Database\Models\User;
use WinLocalInc\Chjs\Tests\Database\Models\Workspace;
use WinLocalInc\Chjs\Tests\TestCase;

class ChargifyHandlePreviewTest extends TestCase
{
    public function testChargifyHandleSubscriptionPreview()
    {
        $workspace = Workspace::factory()->create();
        $paymentProfileId = random_int(1000000, 9999999);
        $user = User::factory()
            ->set(
                'chargify_id',
                random_int(1000000, 9999999)
            )
            ->set(
                'workspace_id',
                $workspace->workspace_id
            )
            ->create();

        $workspace->owner_id = $user->user_id;
        $workspace->save();

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
            ->create();

        SubscriptionComponent::factory()
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

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'current_billing_manifest' => [
                        'line_items' => [[
                            'transaction_type' => 'charge',
                            'kind' => 'baseline',
                            'amount_in_cents' => 0,
                            'memo' => 'Engage (3 Oct 2023 - 3 Nov 2023)',
                            'discount_amount_in_cents' => 0,
                            'taxable_amount_in_cents' => 0,
                            'period_range_start' => '3 Oct 2023',
                            'period_range_end' => '3 Nov 2023',
                            'product_id' => $product->product_id,
                            'product_handle' => 'engage',
                            'product_name' => 'Engage',
                        ], [
                            'transaction_type' => 'charge',
                            'kind' => 'component',
                            'amount_in_cents' => 10000,
                            'memo' => 'Share Card Pro=> 10 pros',
                            'discount_amount_in_cents' => 0,
                            'taxable_amount_in_cents' => 0,
                            'period_range_start' => '3 Oct 2023',
                            'period_range_end' => '3 Nov 2023',
                            'component_id' => $component->component_id,
                            'component_handle' => 'share-card-pro',
                            'component_name' => 'Share Card Pro',
                        ],
                        ],
                        'total_in_cents' => 460000,
                        'total_discount_in_cents' => 0,
                        'total_tax_in_cents' => 0,
                        'subtotal_in_cents' => 460000,
                        'start_date' => '2023-10-03T14=>15=>56Z',
                        'end_date' => '2023-11-03T14=>15=>56Z',
                        'period_type' => 'recurring',
                        'existing_balance_in_cents' => 0,
                    ],
                    'next_billing_manifest' => [
                        'line_items' => [[
                            'transaction_type' => 'charge',
                            'kind' => 'baseline',
                            'amount_in_cents' => 0,
                            'memo' => 'Engage (3 Nov2023 - 3 Dec 2023)',
                            'discount_amount_in_cents' => 0,
                            'taxable_amount_in_cents' => 0,
                            'period_range_start' => '3 Nov 2023',
                            'period_range_end' => '3 Dec 2023',
                            'product_id' => $product->product_id,
                            'product_handle' => 'engage',
                            'product_name' => 'Engage',
                        ], [
                            'transaction_type' => 'charge',
                            'kind' => 'component',
                            'amount_in_cents' => 10000,
                            'memo' => 'Share Card Pro=> 10 pros',
                            'discount_amount_in_cents' => 0,
                            'taxable_amount_in_cents' => 0,
                            'period_range_start' => '3 Nov 2023',
                            'period_range_end' => '3 Dec 2023',
                            'component_id' => $component->component_id,
                            'component_handle' => 'share-card-pro',
                            'component_name' => 'Share Card Pro',
                        ],
                        ],
                        'total_in_cents' => 460000,
                        'total_discount_in_cents' => 0,
                        'total_tax_in_cents' => 0,
                        'subtotal_in_cents' => 460000,
                        'start_date' => '2023-11-03T14=>15=>56Z',
                        'end_date' => '2023-12-03T14=>15=>56Z',
                        'period_type' => 'recurring',
                        'existing_balance_in_cents' => 0,
                    ],
                ], 200),
        ]);

        $response = $workspace->newSubscription(ProductPricing::from($productPrice->product_price_handle))
            ->paymentProfile($paymentProfileId)
            ->component($componentPrice, 10)
            ->preview();

        $this->assertObjectHasProperty('current_billing_manifest', $response);
        $this->assertObjectHasProperty('next_billing_manifest', $response);
    }

    public function testChargifySwapSubscriptionPreview()
    {
        $workspace = Workspace::factory()->create();

        $user = User::factory()
            ->set(
                'chargify_id',
                random_int(1000000, 9999999)
            )
            ->set(
                'workspace_id',
                $workspace->workspace_id
            )
            ->create();

        $workspace->owner_id = $user->user_id;
        $workspace->save();

        $product = Product::factory()->count(1)->has(
            ProductPrice::factory()->count(1),
            'productPrices'
        )->create()
            ->first();

        $productPrice = $product->productPrices()->first();

        $productNew = Product::factory()->count(1)->has(
            ProductPrice::factory()->count(1),
            'productPrices'
        )->create()
            ->first();

        $productPriceNew = $productNew->productPrices()->first();

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
            ->create();

        SubscriptionComponent::factory()
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

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'migration' => [
                        'prorated_adjustment_in_cents' => 0,
                        'charge_in_cents' => 5000,
                        'payment_due_in_cents' => 0,
                        'credit_applied_in_cents' => 0,
                    ],
                ], 200),
        ]);

        $migration = $workspace->swapSubscriptionProductPreview($productPriceNew);

        $this->assertObjectHasProperty('charge_in_cents', $migration);
    }
}
