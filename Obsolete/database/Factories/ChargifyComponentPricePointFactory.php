<?php

namespace Database\Factories\Chargify;

use Illuminate\Support\Str;
use App\Models\Chargify\ChargifyComponentPricePoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargifyComponentPricePointFactory extends Factory
{
    protected $model = ChargifyComponentPricePoint::class;

    public function definition(): array
    {
        return [
            'id' => random_int(1000000, 9999999),
            'name' => Str::random(),
            'pricing_scheme' => array_rand(array_flip([
                'per_unit','volume','tiered','stairstep'
            ])),
            'prices' => [[
                "starting_quantity"=> 1,
                "ending_quantity"=> null,
                "unit_price"=> "10.0",
                "formatted_unit_price"=> "$10.00",
                ]
            ]
        ];
    }
}
