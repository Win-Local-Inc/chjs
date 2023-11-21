<?php

namespace WinLocalInc\Chjs\Concerns;

use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Models\SubscriptionComponent;

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

    public function componentsAllocationPreview(SubscriptionComponent $component, int $qty): ChargifyObject
    {

        return maxio()->subscriptionComponent
            ->allocateComponentsPreview(
                subscriptionId: $this->subscription->subscription_id,
                componentId: $component->component_id,
                pricePoint: $component->component_price_handle,
                qty: $component->subscription_component_quantity + $qty
            );
    }
}
