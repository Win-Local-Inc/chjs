<?php

namespace App\Console\Commands;

use App\Models\Chargify\ChargifyComponent;
use App\Models\Chargify\ChargifyComponentPricePoint;
use App\Models\Chargify\ChargifyProduct;
use App\Models\Chargify\ChargifyProductFamily;
use App\Models\Chargify\ChargifyProductPricePoint;
use App\Services\Chargify\Chargify;
use App\Services\Chargify\ChargifyUtility;
use Illuminate\Console\Command;

class ChargifySync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargify:sync {--sources=products,componentes,coupons,subscriptions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Chargify with --sources=products,componentes,coupons,subscriptions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(protected Chargify $chargify)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $sources = explode(',', $this->option('sources'));
            foreach ($sources as $source) {
                $this->info('updating: '.$source);
                match ($source) {
                    'products' => $this->productSync(),
                    'componentes' => $this->componentSync(),
                    // 'coupons' => '',
                    // 'subscriptions' => '',
                    default => ''
                };
            }
        } catch (\Illuminate\Http\Client\RequestException $exception) {
            $this->error($exception->response->body());
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }

        return 0;
    }

    protected function componentSync()
    {
        $this->updateComponents();
        $this->updateComponentPricePoints();
    }

    protected function updateComponents()
    {
        $parameters = [
            'page' => 1,
            'per_page' => 100,
            'include_archived' => true,
        ];

        do {
            $components = $this->chargify->component()->listComponents($parameters);
            foreach ($components as $component) {
                $this->info('component: '.$component['name']);
                ChargifyComponent::updateOrInsert(
                    [
                        'id' => $component['id'],
                    ],
                    [
                        'chargify_product_family_id' => $component['product_family_id'],
                        'name' => $component['name'],
                        'handle' => $component['handle'],
                        'unit_name' => $component['unit_name'],
                        'kind' => $component['kind'],
                        'archived' => $component['archived'],
                        'created_at' => ChargifyUtility::getFixedDateTime($component['created_at']),
                        'updated_at' => ChargifyUtility::getFixedDateTime($component['updated_at']),
                    ]
                );
            }
            $parameters['page'] += 1;
        } while ($components->count() >= $parameters['per_page']);
    }

    protected function updateComponentPricePoints()
    {
        foreach (['null', 'not_null'] as $achivedAt) {
            $parameters = [
                'page' => 1,
                'per_page' => 100,
                'filter' => [
                    'type' => 'catalog,default,custom',
                    'archived_at' => $achivedAt,
                ],
            ];

            do {
                $componentPricePoints = $this->chargify->component()->listPricePoints($parameters);
                foreach ($componentPricePoints as $componentPricePoint) {
                    $this->info('component price point: '.$componentPricePoint['name']);
                    ChargifyComponentPricePoint::updateOrInsert(
                        [
                            'id' => $componentPricePoint['id'],
                        ],
                        [
                            'chargify_component_id' => $componentPricePoint['component_id'],
                            'name' => $componentPricePoint['name'],
                            'handle' => $componentPricePoint['handle'],
                            'type' => $componentPricePoint['type'],
                            'pricing_scheme' => $componentPricePoint['pricing_scheme'],
                            'prices' => json_encode($componentPricePoint['prices']),
                            'archived_at' => ChargifyUtility::getFixedDateTime($componentPricePoint['archived_at']),
                            'created_at' => ChargifyUtility::getFixedDateTime($componentPricePoint['created_at']),
                            'updated_at' => ChargifyUtility::getFixedDateTime($componentPricePoint['updated_at']),
                        ]
                    );
                }
                $parameters['page'] += 1;
            } while ($componentPricePoints->count() >= $parameters['per_page']);
        }
    }

    protected function productSync()
    {
        $this->updateProductFamilies();
        $this->updateProducts();
        $this->updateProductPricePoints();
    }

    protected function updateProductFamilies()
    {
        $productFamiles = $this->chargify->productFamily()->listProductFamiles();

        foreach ($productFamiles as $productFamily) {
            $this->info('product family: '.$productFamily['name']);
            ChargifyProductFamily::updateOrInsert(
                [
                    'id' => $productFamily['id'],
                ],
                [
                    'name' => $productFamily['name'],
                    'handle' => $productFamily['handle'],
                    'description' => $productFamily['description'],
                    'created_at' => ChargifyUtility::getFixedDateTime($productFamily['created_at']),
                    'updated_at' => ChargifyUtility::getFixedDateTime($productFamily['updated_at']),
                ]
            );
        }
    }

    protected function updateProducts()
    {
        $parameters = [
            'page' => 1,
            'per_page' => 100,
            'include_archived' => true,
        ];

        do {
            $products = $this->chargify->product()->listProducts($parameters);
            foreach ($products as $product) {
                $this->info('product: '.$product['name']);
                ChargifyProduct::updateOrInsert(
                    [
                        'id' => $product['id'],
                    ],
                    [
                        'chargify_product_family_id' => $product['product_family']['id'],
                        'name' => $product['name'],
                        'handle' => $product['handle'],
                        'description' => $product['description'],
                        'require_credit_card' => $product['require_credit_card'],
                        'created_at' => ChargifyUtility::getFixedDateTime($product['created_at']),
                        'updated_at' => ChargifyUtility::getFixedDateTime($product['updated_at']),
                    ]
                );
            }
            $parameters['page'] += 1;
        } while ($products->count() >= $parameters['per_page']);
    }

    protected function updateProductPricePoints()
    {
        foreach (['null', 'not_null'] as $achivedAt) {
            $parameters = [
                'page' => 1,
                'per_page' => 100,
                'filter' => [
                    'type' => 'catalog,default,custom',
                    'archived_at' => $achivedAt,
                ],
            ];

            do {
                $productPricePoints = $this->chargify->productPricePoint()->listPricePoints($parameters);
                foreach ($productPricePoints as $productPricePoint) {
                    $this->info('product price point: '.$productPricePoint['name']);
                    ChargifyProductPricePoint::updateOrInsert(
                        [
                            'id' => $productPricePoint['id'],
                        ],
                        [
                            'chargify_product_id' => $productPricePoint['product_id'],
                            'name' => $productPricePoint['name'],
                            'handle' => $productPricePoint['handle'],
                            'type' => $productPricePoint['type'],
                            'price_in_cents' => $productPricePoint['price_in_cents'],
                            'interval' => $productPricePoint['interval'],
                            'interval_unit' => $productPricePoint['interval_unit'],
                            'trial_price_in_cents' => $productPricePoint['trial_price_in_cents'],
                            'trial_interval' => $productPricePoint['trial_interval'],
                            'trial_interval_unit' => $productPricePoint['trial_interval_unit'],
                            'archived_at' => ChargifyUtility::getFixedDateTime($productPricePoint['archived_at']),
                            'created_at' => ChargifyUtility::getFixedDateTime($productPricePoint['created_at']),
                            'updated_at' => ChargifyUtility::getFixedDateTime($productPricePoint['updated_at']),
                        ]
                    );
                }
                $parameters['page'] += 1;
            } while ($productPricePoints->count() >= $parameters['per_page']);
        }
    }
}
