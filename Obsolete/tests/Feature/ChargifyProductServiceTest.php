<?php

namespace Tests\Feature;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChargifyProductServiceTest extends TestCase
{
    public function testProductServiceCreateSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $name = Str::random();
        $description = Str::random();
        $priceInCents = random_int(10000000, 99999999);
        $interval = 1;
        $intervalUnit = 'month';

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'product' => [
                        'name' => $name,
                        'description' => $description,
                        'price_in_cents' => $priceInCents,
                        'interval' => $interval,
                        'interval_unit' => $intervalUnit,
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->product()->create($productFamilyId, [
            'name' => $name,
            'description' => $description,
            'price_in_cents' => $priceInCents,
            'interval' => $interval,
            'interval_unit' => $intervalUnit,
        ]);

        $this->assertEquals($name, $response['name']);
        $this->assertEquals($description, $response['description']);
        $this->assertEquals($priceInCents, $response['price_in_cents']);
        $this->assertEquals($interval, $response['interval']);
        $this->assertEquals($intervalUnit, $response['interval_unit']);
    }

    public function testProductServiceUpdateSuccess()
    {
        $productId = random_int(10000000, 99999999);
        $name = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'product' => [
                        'name' => $name,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->product()->update($productId, [
            'name' => $name,
        ]);

        $this->assertEquals($name, $response['name']);
    }

    public function testProductServiceArchiveSuccess()
    {
        $productId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'product' => [
                        'name' => Str::random(),
                    ],
                ], 204),
        ]);

        $this->getChargify()->product()->archive($productId);

        Http::assertSentCount(1);
    }

    public function testProductServiceListProductsSuccess()
    {
        $productId1 = random_int(10000000, 99999999);
        $productId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'product' => [
                        'name' => Str::random(),
                        'id' => $productId1,
                    ],
                ], [
                    'product' => [
                        'name' => Str::random(),
                        'id' => $productId2,
                    ],
                ],
                ], 200),
        ]);

        $collection = $this->getChargify()->product()->listProducts([
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertTrue($collection->contains('id', '=', $productId1));
        $this->assertTrue($collection->contains('id', '=', $productId2));
    }

    public function testProductServiceGetByIdSuccess()
    {
        $productId = random_int(10000000, 99999999);
        $name = Str::random();
        $description = Str::random();
        $priceInCents = random_int(10000000, 99999999);
        $interval = 1;
        $intervalUnit = 'month';

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'product' => [
                        'name' => $name,
                        'description' => $description,
                        'price_in_cents' => $priceInCents,
                        'interval' => $interval,
                        'interval_unit' => $intervalUnit,
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->product()->getProductById($productId);

        $this->assertEquals($name, $response['name']);
        $this->assertEquals($description, $response['description']);
        $this->assertEquals($priceInCents, $response['price_in_cents']);
        $this->assertEquals($interval, $response['interval']);
        $this->assertEquals($intervalUnit, $response['interval_unit']);
    }

    public function testProductServiceCreateRequestException()
    {
        $this->expectException(RequestException::class);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'errors' => [
                        'product' => "can't be blank",
                    ],
                ], 422),
        ]);

        $productFamilyId = random_int(10000000, 99999999);
        $name = Str::random();
        $description = Str::random();
        $priceInCents = random_int(10000000, 99999999);
        $interval = 1;
        $intervalUnit = 'month';

        $this->getChargify()->product()->create($productFamilyId, [
            'name' => $name,
            'description' => $description,
            'price_in_cents' => $priceInCents,
            'interval' => $interval,
            'interval_unit' => $intervalUnit,
        ]);
    }

    public function testCustomerServiceCreateValidationException()
    {
        $this->expectException(ValidationException::class);
        $productFamilyId = random_int(10000000, 99999999);
        $name = Str::random();
        $this->getChargify()->product()->create($productFamilyId, [
            'name' => $name,
        ]);
    }
}
