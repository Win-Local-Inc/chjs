<?php

namespace WinLocalInc\Chjs\Database\Factoriess;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Models\ComponentPrice;

class ComponentPriceFactory extends Factory
{
    protected $model = ComponentPrice::class;

    public function definition(): array
    {
        return [
            'component_price_id' => random_int(1000000, 9999999),
            'component_handle' => Str::random(),
            'component_price_handle' => Str::random(),
            'component_price_name' => Str::random(),
            'component_price_type' => Str::random(),
            'component_price_scheme' => array_rand(array_flip([
                'per_unit', 'volume', 'tiered', 'stairstep',
            ])),
        ];
    }
}
