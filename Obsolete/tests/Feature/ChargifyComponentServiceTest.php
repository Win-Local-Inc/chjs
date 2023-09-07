<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifyComponentServiceTest extends TestCase
{
    public function testComponentServiceCreateComponentSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $componentId = random_int(10000000, 99999999);
        $name = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'component' => [
                        'id' => $componentId,
                        'name' => $name,
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->component()->createComponent($productFamilyId, 'metered_component', [
            'name' => $name,
            'unit_name' => 'text message',
            'taxable' => false,
            'pricing_scheme' => 'stairstep',
            'prices' => [
                [
                    'starting_quantity' => 1,
                    'unit_price' => 1,
                ],
            ],
        ]);

        $this->assertEquals($componentId, $response['id']);
    }

    public function testComponentServiceListComponentsSuccess()
    {
        $componentId1 = random_int(10000000, 99999999);
        $componentId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'component' => [
                        'id' => $componentId1,
                    ],
                ], [
                    'component' => [
                        'id' => $componentId2,
                    ],
                ],
                ], 200),
        ]);

        $collection = $this->getChargify()->component()->listComponents([
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertTrue($collection->contains('id', '=', $componentId1));
        $this->assertTrue($collection->contains('id', '=', $componentId2));
    }
}
