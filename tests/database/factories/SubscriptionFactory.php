<?php

namespace WinLocalInc\Chjs\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Models\Subscription;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'subscription_id' => random_int(1000000, 9999999),
            'product_handle' => Str::random(),
        ];
    }
}
