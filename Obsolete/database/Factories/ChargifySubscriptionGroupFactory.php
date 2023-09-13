<?php

namespace Database\Factories\Chargify;

use App\Models\Chargify\ChargifySubscriptionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChargifySubscriptionGroupFactory extends Factory
{
    protected $model = ChargifySubscriptionGroup::class;

    public function definition(): array
    {
        return [
            'id' => 'grp_'.Str::random(),
            'state' => 'active',
        ];
    }
}
