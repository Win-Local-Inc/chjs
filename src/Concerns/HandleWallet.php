<?php

namespace WinLocalInc\Chjs\Concerns;

use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Exceptions\InvalidCustomer;
use WinLocalInc\Chjs\Models\ComponentPrice;

trait HandleWallet
{
    /**
     * @throws InvalidCustomer
     */
    public function addBalanceToWallet($qty): ChargifyObject
    {
        $adComponentPrice = ComponentPrice::where('component_handle', 'ad_credit_one_time')->first();

        $data = ['component_id' => $adComponentPrice->component_id, 'quantity' => $qty];

        return maxio()->subscriptionComponent->updateQuantity(
            subscriptionId: $this->subscription->subscription_id,
            componentId: $adComponentPrice->component_id,
            options: $data
        );

    }
}
