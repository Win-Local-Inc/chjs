<?php

namespace WinLocalInc\Chjs\Concerns;

use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Models\ProductPrice;

trait HandlePreview
{
    public function swapSubscriptionProductPreview(ProductPrice $productPrice, int $customPrice = null): ChargifyObject
    {
        $data = ['product_id' => $productPrice->product_id];

        if (! $customPrice) {
            $data['product_price_point_id'] = $productPrice->product_price_id;
        } else {
            $data['custom_price']['price_in_cents'] = $customPrice;
            $data['custom_price']['interval'] = $productPrice->product_price_interval->getInterval();
            $data['custom_price']['interval_unit'] = 'month';
        }

        return maxio()->subscription
            ->migrateProductPreview(
                subscriptionId: $this->subscription->subscription_id,
                parameters: $data
            );
    }

    public function componentsAllocationPreview(array $components): ChargifyObject
    {
        /**
         * array $components
         *  [
         *       [
         *          "component_id" => 2341717,
         *          "quantity" => 40,
         *          "price_point_id" => 2985214
         *          "upgrade_charge" => "full",
         *          "downgrade_credit" => "full",
         *          "accrue_charge" => false,
         *       ],
         *       [
         *          "component_id" => 2341719,
         *          "quantity" => 10,
         *          "custom_price" => [
         *               'pricing_scheme' => 'per_unit',
         *               'prices' => [
         *                   [
         *                   "starting_quantity" => 1,
         *                   "unit_price" => 250,
         *                   ],
         *               ]
         *           ]
         *       ]
         */

        return maxio()->subscriptionComponent
            ->allocateComponentsPreview(
                subscriptionId: $this->subscription->subscription_id,
                parameters: ['allocations' => $components]
            );
    }
}
