<?php

namespace WinLocalInc\Chjs\Console;

use Illuminate\Console\Command;
use WinLocalInc\Chjs\Chargify\PricePoints;
use WinLocalInc\Chjs\Enums\IsActive;
use WinLocalInc\Chjs\Enums\MainComponent;
use WinLocalInc\Chjs\Enums\SubscriptionInterval;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Product;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;
use WinLocalInc\Chjs\ProductStructure;
use WinLocalInc\Chjs\Webhook\ChargifyUtility;

use function Laravel\Prompts\multiselect;

class MaxioSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maxio:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync maxio';

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
            $this->info('updating: ' . $source);
            match ($source) {
                'Products' => $this->productSync(),
                'Subscription' => $this->subscriptionSync(),
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

    protected function subscriptionSync()
    {
        $uniqueWorkspace = [];

        $parameters = [
            'page' => 1,
            'per_page' => 50,
            'direction' => 'desc',
        ];

        do {

            $this->info('subscriptionSync: per page - ' . $parameters['per_page'] . ' page number - ' . $parameters['page']);

            $subscriptions = maxio()
                ->subscription
                ->list($parameters)
                ->filter(function ($subscription) use (&$uniqueWorkspace) {
                    if (!$subscription->reference || in_array($subscription->reference, $uniqueWorkspace)) {
                        return false;
                    }

                    $uniqueWorkspace[] = $subscription->reference;

                    return true;
                });

            $upsertSubscriptions = $subscriptions->map(function ($subscription) {
                return [
                    'subscription_id' => $subscription->id,
                    'workspace_id' => $subscription->reference,
                    'user_id' => $subscription->customer->reference,
                    'product_price_handle' => $subscription->product->product_price_point_handle,
                    'product_handle' => $subscription->product->handle,
                    'status' => $subscription->state,
                    'payment_collection_method' => $subscription->payment_collection_method,
                    'subscription_interval' => SubscriptionInterval::getIntervalUnit((int) $subscription->product->interval)->value,
                    'total_revenue_in_cents' => $subscription->total_revenue_in_cents,
                    'next_billing_at' => $subscription->next_assessment_at,
                    'created_at' => $subscription->created_at,
                    'updated_at' => $subscription->updated_at,

                ];
            })->toArray();

            Subscription::upsert($upsertSubscriptions, ['workspace_id']);

            $subscriptionMap = array_reduce($upsertSubscriptions, function ($carry, $item) {
                $carry[$item['subscription_id']] = $item['product_handle'];

                return $carry;
            }, []);

            $this->updateSubscriptionComponents($subscriptionMap);

            $parameters['page'] += 1;
        } while ($subscriptions->count() >= $parameters['per_page']);

        $this->info("subscriptions sync done!\n");
    }

    protected function updateSubscriptionComponents(array &$subscriptionMap)
    {
        $componentsArray = collect([]);

        $parameters = [
            'page' => 1,
            'per_page' => 100,
            'subscription_ids' => implode(',', array_keys($subscriptionMap)),
        ];
        do {
            $response = maxio()->subscriptionComponent->listForSite($parameters);
            $componentsArray = $componentsArray->concat($response);
            $parameters['page'] += 1;
        } while ($response->count() >= $parameters['per_page']);

        $componentPrices = collect([]);

        foreach ($componentsArray->pluck('price_point_id')->unique()->chunk(100) as $pricePointIds) {
            $parameters = [
                'page' => 1,
                'per_page' => 100,
                'filter' => [
                    'ids' => implode(',', $pricePointIds->toArray()),
                    'type' => 'catalog,default,custom',
                ],
            ];
            $response = maxio()->componentPrice->list($parameters);
            $componentPrices = $componentPrices->concat($response);
        }

        $pricesMap = $componentPrices->reduce(function (array $carry, PricePoints $item) {
            $carry[$item->component_id] = $item->prices->first()->unit_price;

            return $carry;
        }, []);

        $upsertComponents = $componentsArray->map(function ($component) use (&$pricesMap, &$subscriptionMap) {
            return [
                'subscription_id' => $component->subscription_id,
                'component_id' => $component->component_id,
                'component_handle' => $component->component_handle,
                'component_price_handle' => $component->price_point_handle,
                'component_price_id' => $component->price_point_id,
                'subscription_component_price' => $pricesMap[$component->component_id],
                'subscription_component_quantity' => $component->allocated_quantity,
                'is_main_component' => ProductStructure::isMainComponent(product: $subscriptionMap[$component->subscription_id], component: $component->component_handle),
                'created_at' => ChargifyUtility::getFixedDateTime($component->created_at),
                'updated_at' => ChargifyUtility::getFixedDateTime($component->updated_at),
            ];
        })->toArray();

        SubscriptionComponent::upsert($upsertComponents, ['subscription_id', 'component_id']);
    }

    protected function updateProducts()
    {
        $parameters = [
            'page' => 1,
            'per_page' => 100,
        ];

        do {
            $products = maxio()->product->list($parameters);

            foreach ($products as $product) {
                $this->info('product: ' . $product->name);
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
            $parameters['page'] += 1;
        } while ($products->count() >= $parameters['per_page']);

        $this->info("products sync done!\n");
    }

    protected function updateProductPrice(): void
    {
        $parameters = [
            'page' => 1,
            'per_page' => 100,
        ];

        do {

            $productPricePoints = maxio()->productPrice->list($parameters);

            foreach ($productPricePoints as $productPricePoint) {
                $this->info('product price: ' . $productPricePoint->name);
                $productHandle = Product::find($productPricePoint->product_id)?->product_handle;
                if (!$productHandle) {
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

            $parameters['page'] += 1;
        } while ($productPricePoints->count() >= $parameters['per_page']);

        $this->info("product prices sync done!\n");

    }

    protected function updateComponents()
    {
        $parameters = [
            'page' => 1,
            'per_page' => 100,
        ];

        do {
            $components = maxio()->component->list($parameters);
            foreach ($components as $component) {
                $this->info('component: ' . $component->name);
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

            $parameters['page'] += 1;
        } while ($components->count() >= $parameters['per_page']);

        $this->info("components sync done!\n");
    }

    protected function updateComponentPrice()
    {
        $parameters = [
            'page' => 1,
            'per_page' => 100,
        ];

        do {

            $componentPricePoints = maxio()->componentPrice->list($parameters);

            foreach ($componentPricePoints as $componentPricePoint) {
                $this->info('component price: ' . $componentPricePoint->name);

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
            $parameters['page'] += 1;
        } while ($componentPricePoints->count() >= $parameters['per_page']);

        $this->info("component prices sync done!\n");
    }
}
