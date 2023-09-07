<?php

namespace Database\Factories\Chargify;

use Illuminate\Support\Str;
use App\Models\Chargify\ChargifyProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargifyProductFactory extends Factory
{
    protected $model = ChargifyProduct::class;

    public function definition(): array
    {
        return [
            'id' => random_int(1000000, 9999999),
            'name' => Str::random(),
            'handle' => Str::random(),
            'require_credit_card' => true,
        ];
    }
}
