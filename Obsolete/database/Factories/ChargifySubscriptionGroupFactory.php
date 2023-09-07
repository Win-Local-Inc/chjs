<?php

namespace Database\Factories\Chargify;

use Illuminate\Support\Str;
use App\Models\Chargify\ChargifySubscriptionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargifySubscriptionGroupFactory extends Factory
{
    protected $model = ChargifySubscriptionGroup::class;

    public function definition(): array
    {
        return [
            'id' => 'grp_'.Str::random(),
            'state' => 'active'
        ];
    }
}
