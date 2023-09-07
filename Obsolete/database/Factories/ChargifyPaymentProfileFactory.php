<?php

namespace Database\Factories\Chargify;

use App\Models\Chargify\ChargifyPaymentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargifyPaymentProfileFactory extends Factory
{
    protected $model = ChargifyPaymentProfile::class;

    public function definition(): array
    {
        return [
            'id' => random_int(1000000, 9999999),
            'is_default' => true,
        ];
    }
}
