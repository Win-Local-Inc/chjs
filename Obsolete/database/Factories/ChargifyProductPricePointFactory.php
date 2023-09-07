<?php

namespace Database\Factories\Chargify;

use Illuminate\Support\Str;
use App\Models\Chargify\ChargifyProductPricePoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargifyProductPricePointFactory extends Factory
{
    protected $model = ChargifyProductPricePoint::class;

    public function definition(): array
    {
        return [
            'id' => random_int(1000000, 9999999),
            'name' => Str::random(),
            'handle' => Str::random(),
            'price_in_cents' => random_int(1000, 9999),
            'interval' => 1,
            'interval_unit' => 'month',
        ];
    }
}
