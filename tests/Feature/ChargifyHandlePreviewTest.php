<?php

namespace WinLocalInc\Chjs\Tests\Feature;

use Illuminate\Support\Facades\Http;
use WinLocalInc\Chjs\Database\Seeders\ComponentSeeder;
use WinLocalInc\Chjs\Database\Seeders\ProductSeeder;
use WinLocalInc\Chjs\Enums\Product as ProductEnum;
use WinLocalInc\Chjs\Enums\ProductPricing;
use WinLocalInc\Chjs\Enums\ShareCardProPricing;
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
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
        $this->seed(ComponentSeeder::class);
    }

    public function testChargifyHandleSubscriptionPreview()
    {
        $workspace = Workspace::factory()->create();
        $paymentProfileId = random_int(1000000, 9999999);
        $user = User::factory()->workspace($workspace)->create();
        $workspace->owner_id = $user->user_id;
        $workspace->save();

        $product = Product::first();

        $productPrice = $product->productPrices->first();

        $component = Component::first();

        $componentPrice = $component->price->first();

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

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'current_billing_manifest' => [
                        'line_items' => [[
                            'transaction_type' => 'charge',
                            'kind' => 'baseline',
                            'amount_in_cents' => 0,
                            'memo' => $product->product_name.' (3 Oct 2023 - 3 Nov 2023)',
                            'discount_amount_in_cents' => 0,
                            'taxable_amount_in_cents' => 0,
                            'period_range_start' => '3 Oct 2023',
                            'period_range_end' => '3 Nov 2023',
                            'product_id' => $product->product_id,
                            'product_handle' => $product->product_handle,
                            'product_name' => $product->product_name,
                        ], [
                            'transaction_type' => 'charge',
                            'kind' => 'component',
                            'amount_in_cents' => 10000,
                            'memo' => $component->component_name.' => 10 pros',
                            'discount_amount_in_cents' => 0,
                            'taxable_amount_in_cents' => 0,
                            'period_range_start' => '3 Oct 2023',
                            'period_range_end' => '3 Nov 2023',
                            'component_id' => $component->component_id,
                            'component_handle' => $component->component_handle,
                            'component_name' => $component->component_name,
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
                            'memo' => $product->product_name.' (3 Oct 2023 - 3 Nov 2023)',
                            'discount_amount_in_cents' => 0,
                            'taxable_amount_in_cents' => 0,
                            'period_range_start' => '3 Nov 2023',
                            'period_range_end' => '3 Dec 2023',
                            'product_id' => $product->product_id,
                            'product_handle' => $product->product_handle,
                            'product_name' => $product->product_name,
                        ], [
                            'transaction_type' => 'charge',
                            'kind' => 'component',
                            'amount_in_cents' => 10000,
                            'memo' => $component->component_name.' => 10 pros',
                            'discount_amount_in_cents' => 0,
                            'taxable_amount_in_cents' => 0,
                            'period_range_start' => '3 Nov 2023',
                            'period_range_end' => '3 Dec 2023',
                            'component_id' => $component->component_id,
                            'component_handle' => $component->component_handle,
                            'component_name' => $component->component_name,
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

        $response = $workspace->newSubscription($productPrice->product_price_handle)
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
            ->workspace($workspace)
            ->withChargifyId()
            ->create();

        $workspace->owner_id = $user->user_id;
        $workspace->save();

        $productPrice = ProductPrice::where('product_price_handle', ProductPricing::PROMO_MONTH->value)->first();

        $productPriceNew = ProductPrice::where('product_price_handle', ProductPricing::SOLO_MONTH->value)->first();

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
