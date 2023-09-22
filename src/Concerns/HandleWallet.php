<?php

namespace WinLocalInc\Chjs\Concerns;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Exceptions\CustomerAlreadyCreated;
use WinLocalInc\Chjs\Exceptions\InvalidCustomer;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;

trait HandleWallet
{


    /**
     * @throws InvalidCustomer
     */
    public function applyBalance($qty)
    {
        $adComponentPrice = ComponentPrice::where('component_handle', config('chjs.ads_component'))->first();

        $data = ['component_id' => $adComponentPrice->component_id, 'quantity' => $qty];

        maxio()->subscriptionComponent->updateQuantity(
            subscriptionId: $this->subscription->subscription_id,
            componentId: $adComponentPrice->component_id,
            options: $data
        );

        $maxioComponent = maxio()->subscriptionComponent
            ->list(subscriptionId: $this->subscription->subscription_id)
            ->where('component_id', $adComponentPrice->component_id)->first();

        $componentPrice = maxio()->componentPrice->list(['filter' => ['ids' => $maxioComponent->price_point_id]]);

        SubscriptionComponent::updateOrCreate(
            [   'subscription_id' => $this->subscription->subscription_id,
                'component_id' => $maxioComponent->component_id,
            ],
            [
                'component_handle' => $maxioComponent->component_handle,
                'component_price_handle' => $maxioComponent->price_point_handle,
                'component_price_id' => $maxioComponent->price_point_id,
                'subscription_component_price' => $componentPrice->first()->prices->first()->unit_price,
                'subscription_component_quantity' => $maxioComponent->allocated_quantity,
                'created_at' => $maxioComponent->created_at,
                'updated_at' => $maxioComponent->updated_at,
            ]
        );

        return $this;
    }

    //todo check other subscription if exists for balance
    public function applyBalanceUsage($mount)
    {
        $adComponentPrice = ComponentPrice::where('component_handle', config('chjs.ads_component'))->first();

        $maxioComponent = maxio()->subscriptionComponent
            ->list(subscriptionId: $this->subscription->subscription_id)
            ->where('component_id', $adComponentPrice->component_id)->first();

        if($maxioComponent->allocated_quantity - $maxioComponent->unit_balance - $mount < 0)
        {
            throw new \Exception("request amount is higher than user balance");
        }

        maxio()->subscriptionComponent->createUsage(
            subscriptionId: $this->subscription->subscription_id,
            componentId: $adComponentPrice->component_id,
            qty: $mount
        );

        $maxioComponent = maxio()->subscriptionComponent
            ->list(subscriptionId: $this->subscription->subscription_id)
            ->where('component_id', $adComponentPrice->component_id)->first();

        $componentPrice = maxio()->componentPrice->list(['filter' => ['ids' => $maxioComponent->price_point_id]]);

        SubscriptionComponent::updateOrCreate(
            [   'subscription_id' => $this->subscription->subscription_id,
                'component_id' => $maxioComponent->component_id,
            ],
            [
                'component_handle' => $maxioComponent->component_handle,
                'component_price_handle' => $maxioComponent->price_point_handle,
                'component_price_id' => $maxioComponent->price_point_id,
                'subscription_component_price' => $componentPrice->first()->prices->first()->unit_price,
                'subscription_component_quantity' => $maxioComponent->allocated_quantity - $maxioComponent->unit_balance,
                'created_at' => $maxioComponent->created_at,
                'updated_at' => $maxioComponent->updated_at,
            ]
        );

        return $this;
    }

    public function balance(): int
    {
        $adComponent = $this->subscription->components()
            ->where('component_handle', config('chjs.ads_component'))->first();

        return $adComponent ?  (int) ($adComponent->subscription_component_quantity * 100) : 0;
    }



}
