<?php

namespace Database\Factories\Chargify;

use App\Models\Chargify\ChargifyEvent;
use App\Services\Chargify\Enums\WebhookEvents;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChargifyEventFactory extends Factory
{
    protected $model = ChargifyEvent::class;

    public function definition(): array
    {
        return [
            'id' => random_int(1000000, 9999999),
            'event_name' => WebhookEvents::CustomerCreate->value,
            'payload' => [
                'customer' => [
                    'email' => $this->faker->email(),
                    'first_name' => $this->faker->firstName(),
                    'id' => random_int(1000000, 9999999),
                    'last_name' => $this->faker->lastName(),
                ],
                'site' => [
                    'id' => random_int(1000000, 9999999),
                    'subdomain' => $this->faker->word(),
                ],
            ],
        ];
    }
}
