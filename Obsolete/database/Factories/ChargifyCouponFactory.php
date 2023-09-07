<?php

namespace Database\Factories\Chargify;

use Illuminate\Support\Str;
use App\Models\Chargify\ChargifyCoupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargifyCouponFactory extends Factory
{
    protected $model = ChargifyCoupon::class;

    public function definition(): array
    {
        return [
            'id' => random_int(1000000, 9999999),
            'name' => Str::random(),
            'code' => Str::random(),
            'amount_in_cents' => random_int(1000000, 9999999),
        ];
    }
}
