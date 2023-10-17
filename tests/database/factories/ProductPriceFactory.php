<?php

namespace WinLocalInc\Chjs\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Enums\ProductPricing;
use WinLocalInc\Chjs\Models\ProductPrice;

class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    public function definition(): array
    {
        return [
            'product_price_id' => random_int(1000000, 9999999),
            'product_handle' => Str::random(),
            'product_price_handle' => array_rand(array_flip(ProductPricing::values())),
            'product_price_name' => Str::random(),
        ];
    }
}
