<?php

namespace WinLocalInc\Chjs\Database\Factoriess;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;

class SubscriptionComponentFactory extends Factory
{
    protected $model = SubscriptionComponent::class;

    public function definition(): array
    {
        return [
            'component_handle' => Str::random(),
            'component_price_handle' => Str::random(),
        ];
    }

    public function subscription(Subscription $subscription): SubscriptionComponentFactory
    {
        return $this->state(['subscription_id' => $subscription->subscription_id]);
    }

    public function component(Component $component): SubscriptionComponentFactory
    {
        return $this->state(['component_id' => $component->component_id, 'component_handle' => $component->component_handle]);
    }

    public function componentPrice(ComponentPrice $componentPrice): SubscriptionComponentFactory
    {
        return $this->state(['component_price_id' => $componentPrice->component_price_id, 'component_price_handle' => $componentPrice->component_price_handle]);
    }
}
