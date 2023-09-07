<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifyProductPricePointServiceTest extends TestCase
{
    public function testProductPricePointServiceCreateSuccess()
    {
        $productId = random_int(10000000, 99999999);
        $name = Str::random();
        $priceInCents = random_int(10000000, 99999999);
        $interval = 1;
        $intervalUnit = 'month';

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'price_point' => [
                        'name' => $name,
                        'price_in_cents' => $priceInCents,
                        'interval' => $interval,
                        'interval_unit' => $intervalUnit,
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->productPricePoint()->create($productId, [
            'name' => $name,
            'price_in_cents' => $priceInCents,
            'interval' => $interval,
            'interval_unit' => $intervalUnit,
        ]);

        $this->assertEquals($name, $response['name']);
        $this->assertEquals($priceInCents, $response['price_in_cents']);
        $this->assertEquals($interval, $response['interval']);
        $this->assertEquals($intervalUnit, $response['interval_unit']);
    }

    public function testProductPricePointServiceUpdateSuccess()
    {
        $productId = random_int(10000000, 99999999);
        $pricePointId = random_int(10000000, 99999999);
        $name = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'price_point' => [
                        'name' => $name,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->productPricePoint()->update($productId, $pricePointId, [
            'name' => $name,
        ]);

        $this->assertEquals($name, $response['name']);
    }

    public function testProductPricePointServiceArchiveSuccess()
    {
        $productId = random_int(10000000, 99999999);
        $pricePointId = random_int(10000000, 99999999);
        $name = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'price_point' => [
                        'name' => $name,
                    ],
                ], 204),
        ]);

        $response = $this->getChargify()->productPricePoint()->archive($productId, $pricePointId);

        $this->assertEquals($name, $response['name']);
    }

    public function testProductPricePointServiceListProductsSuccess()
    {
        $pricePointId1 = random_int(10000000, 99999999);
        $pricePointId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push(['price_points' => [[
                    'name' => Str::random(),
                    'id' => $pricePointId1,
                ], [
                    'name' => Str::random(),
                    'id' => $pricePointId2,
                ],
                ]], 200),
        ]);

        $collection = $this->getChargify()->productPricePoint()->listPricePoints([
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertTrue($collection->contains('id', '=', $pricePointId1));
        $this->assertTrue($collection->contains('id', '=', $pricePointId2));
    }

    public function testProductPricePointServiceGetByIdSuccess()
    {
        $productId = random_int(10000000, 99999999);
        $pricePointId = random_int(10000000, 99999999);
        $name = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'price_point' => [
                        'name' => $name,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->productPricePoint()->getPricePointById($productId, $pricePointId);

        $this->assertEquals($name, $response['name']);
    }
}
