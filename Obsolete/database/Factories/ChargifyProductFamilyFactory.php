<?php

namespace Database\Factories\Chargify;

use Illuminate\Support\Str;
use App\Models\Chargify\ChargifyProductFamily;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargifyProductFamilyFactory extends Factory
{
    protected $model = ChargifyProductFamily::class;

    public function definition(): array
    {
        return [
            'id' => random_int(1000000, 9999999),
            'name' => Str::random(),
            'handle' => Str::random()
        ];
    }
}
