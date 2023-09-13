<?php

namespace Database\Factories\Chargify;

use App\Models\Chargify\ChargifySubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargifySubscriptionFactory extends Factory
{
    protected $model = ChargifySubscription::class;

    public function definition(): array
    {
        return [
            'id' => random_int(1000000, 9999999),
            'state' => 'active',
        ];
    }
}
