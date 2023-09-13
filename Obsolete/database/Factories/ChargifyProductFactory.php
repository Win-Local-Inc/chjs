<?php

namespace Database\Factories\Chargify;

use App\Models\Chargify\ChargifyProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
