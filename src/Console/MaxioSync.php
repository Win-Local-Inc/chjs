<?php

namespace WinLocalInc\Chjs\Console;

use Illuminate\Console\Command;
use WinLocalInc\Chjs\Enums\IsActive;
use WinLocalInc\Chjs\Enums\MainComponent;
use WinLocalInc\Chjs\Enums\SubscriptionInterval;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Product;
use WinLocalInc\Chjs\Models\ProductPrice;

use function Laravel\Prompts\multiselect;

class MaxioSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maxio:sync {--sources?=products,components,coupons,subscriptions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync maxio with --sources=products,components,coupons,subscriptions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sources = multiselect(
            label: 'What should we sync?',
            options: ['Products', 'Subscription'],
            default: ['Products']
        );

        foreach ($sources as $source) {
            $this->info('updating: '.$source);
            match ($source) {
                'Products' => $this->productSync(),
                // 'Subscription' => '',
            };
        }

        return 0;
    }

    protected function productSync(): void
    {
        $this->updateProducts();
        $this->updateProductPrice();
        $this->updateComponents();
        $this->updateComponentPrice();
    }

    protected function updateProducts()
    {
        $products = maxio()->product->list();

        foreach ($products as $product) {
            $this->info('product: '.$product->name);
            Product::updateOrInsert(
                [
                    'product_id' => $product->id,
                ],
                [
                    'product_handle' => $product->handle,
                    'product_name' => $product->name,
                    'product_is_active' => is_null($product->archived_at) ? IsActive::Active : IsActive::Inactive,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ]
            );
        }

        $this->info("products sync done!\n");

    }

    protected function updateProductPrice(): void
    {
        $productPricePoints = maxio()->productPrice->list();

        foreach ($productPricePoints as $productPricePoint) {
            $this->info('product price: '.$productPricePoint->name);
            $productHandle = Product::find($productPricePoint->product_id)?->product_handle;
            if (! $productHandle) {
                continue;
            }

            ProductPrice::updateOrInsert(
                [
                    'product_price_id' => $productPricePoint->id,
                ],
                [
                    'product_id' => $productPricePoint->product_id,
                    'product_handle' => $productHandle,
                    'product_price_handle' => $productPricePoint->handle,
                    'product_price_name' => $productPricePoint->name,
                    'product_price_interval' => SubscriptionInterval::getIntervalUnit($productPricePoint->interval),
                    'product_price_in_cents' => $productPricePoint->price_in_cents,
                    'product_price_is_active' => is_null($productPricePoint->archived_at) ? IsActive::Active : IsActive::Inactive,
                    'created_at' => $productPricePoint->created_at,
                    'updated_at' => $productPricePoint->updated_at,
                ]
            );
        }

        $this->info("product prices sync done!\n");

    }

    protected function updateComponents()
    {
        $components = maxio()->component->list();
        foreach ($components as $component) {
            $this->info('component: '.$component->name);
            $componentData = [
                'component_handle' => $component->handle,
                'component_name' => $component->name,
                'component_unit' => $component->unit_name,
                'component_type' => $component->kind,
                'component_is_active' => is_null($component->archived_at) ? IsActive::Active : IsActive::Inactive,
                'created_at' => $component->created_at,
                'updated_at' => $component->updated_at,
            ];

            try {
                $componentData['component_entry'] = MainComponent::findComponent($componentData['component_handle'])->name;
            } catch (\Throwable $e) {
            }

            Component::updateOrInsert(
                ['component_id' => $component->id],
                $componentData
            );
        }

        $this->info("components sync done!\n");
    }

    protected function updateComponentPrice()
    {
        $componentPricePoints = maxio()->componentPrice->list();

        foreach ($componentPricePoints as $componentPricePoint) {
            $this->info('component price: '.$componentPricePoint->name);

            ComponentPrice::updateOrInsert(
                [
                    'component_price_id' => $componentPricePoint->id,
                ],
                [
                    'component_id' => $componentPricePoint->component_id,
                    'component_handle' => Component::find($componentPricePoint->component_id)->component_handle,
                    'component_price_handle' => $componentPricePoint->handle,
                    'component_price_name' => $componentPricePoint->name,
                    'component_price_scheme' => $componentPricePoint->pricing_scheme,
                    'component_price_type' => $componentPricePoint->type,
                    'component_price_in_cents' => $componentPricePoint->prices->first()->unit_price,
                    'component_price_is_active' => is_null($componentPricePoint->archived_at) ? IsActive::Active : IsActive::Inactive,
                    'created_at' => $componentPricePoint->created_at,
                    'updated_at' => $componentPricePoint->updated_at,
                ]
            );
        }

        $this->info("component prices sync done!\n");
    }
}
