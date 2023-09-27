<?php

namespace WinLocalInc\Chjs\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'product_id' => random_int(1000000, 9999999),
            'product_handle' => Str::random(),
            'product_name' => Str::random(),
        ];
    }
}
