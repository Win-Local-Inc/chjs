<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifyCustomFieldsServiceTest extends TestCase
{
    public function testCustomFieldsServiceCreateComponentSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);
        $name = Str::random();
        $value = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'id' => random_int(10000000, 99999999),
                    'metafield_id' => random_int(10000000, 99999999),
                    'value' => $value,
                    'name' => $name,
                ],
                ], 200),
        ]);

        $response = $this->getChargify()->customFields()->createMedadata(
            $subscriptionId,
            'subscriptions',
            [
                [
                    'name' => $name,
                    'value' => $value,
                ],
            ]
        );

        $this->assertEquals($name, $response[0]['name']);
    }
}
