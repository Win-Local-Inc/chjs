<?php

namespace Database\Factories\Chargify;

use App\Models\Chargify\ChargifyCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargifyCustomerFactory extends Factory
{
    protected $model = ChargifyCustomer::class;

    public function definition(): array
    {
        return [
            'id' => random_int(1000000, 9999999),
        ];
    }
}
