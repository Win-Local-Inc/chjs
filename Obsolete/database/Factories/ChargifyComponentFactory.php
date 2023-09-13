<?php

namespace Database\Factories\Chargify;

use App\Models\Chargify\ChargifyComponent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChargifyComponentFactory extends Factory
{
    protected $model = ChargifyComponent::class;

    public function definition(): array
    {
        return [
            'id' => random_int(1000000, 9999999),
            'name' => Str::random(),
            'unit_name' => Str::random(),
            'kind' => array_rand(array_flip([
                'metered_component',
                'quantity_based_component',
                'on_off_component',
                'prepaid_usage_component',
                'event_based_component'])),
        ];
    }
}
