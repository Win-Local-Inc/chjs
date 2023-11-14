<?php

namespace WinLocalInc\Chjs\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Enums\MainComponent;
use WinLocalInc\Chjs\Enums\ShareCardProPricing;
use WinLocalInc\Chjs\Models\Component;

class ComponentFactory extends Factory
{
    protected $model = Component::class;

    public function definition(): array
    {
        return [
            'component_id' => random_int(1000000, 9999999),
            'component_handle' => ShareCardProPricing::MONTH->value,
            'component_name' => Str::random(),
            'component_entry' => MainComponent::SHARE_CARD_PRO->name,
            'component_unit' => Str::random(10),
            'component_type' => array_rand(array_flip([
                'metered_component',
                'quantity_based_component',
                'on_off_component',
                'prepaid_usage_component',
                'event_based_component'])),
        ];
    }
}
