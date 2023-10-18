<?php

namespace WinLocalInc\Chjs\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
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
}
