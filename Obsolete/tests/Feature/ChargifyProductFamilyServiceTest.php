<?php

namespace Tests\Feature;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChargifyProductFamilyServiceTest extends TestCase
{
    public function testProductFamilyServiceCreateSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $name = Str::random();
        $description = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'product_family' => [
                        'id' => $productFamilyId,
                        'name' => $name,
                        'description' => $description,
                        'handle' => $name,
                        'accounting_code' => null,
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->productFamily()->create([
            'name' => $name,
            'description' => $description,
        ]);

        $this->assertEquals($productFamilyId, $response['id']);
        $this->assertEquals($name, $response['name']);
        $this->assertEquals($description, $response['description']);
    }

    public function testProductFamilyServiceListProductFamiliesSuccess()
    {
        $familyId1 = random_int(10000000, 99999999);
        $familyId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    [
                        'product_family' => [
                            'id' => $familyId1,
                            'name' => 'Acme Projects',
                            'description' => null,
                            'handle' => 'acme-projects',
                            'accounting_code' => null,
                            'created_at' => '2013-02-20T15:05:51-07:00',
                            'updated_at' => '2013-02-20T15:05:51-07:00',
                        ],
                    ],
                    [
                        'product_family' => [
                            'id' => $familyId2,
                            'name' => 'Bat Family',
                            'description' => 'Another family.',
                            'handle' => 'bat-family',
                            'accounting_code' => null,
                            'created_at' => '2014-04-16T12:41:13-06:00',
                            'updated_at' => '2014-04-16T12:41:13-06:00',
                        ],
                    ],
                ], 200),
        ]);

        $collection = $this->getChargify()->productFamily()->listProductFamiles([
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertTrue($collection->contains('id', '=', $familyId1));
        $this->assertTrue($collection->contains('id', '=', $familyId2));
    }

    public function testCustomerServiceListProductsForProductFamilySuccess()
    {
        $familyId = random_int(10000000, 99999999);
        $productId1 = random_int(10000000, 99999999);
        $productId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    [
                        'product' => [
                            'id' => $productId1,
                            'name' => 'Free product',
                            'product_family' => [
                                'id' => 527890,
                            ],
                            'public_signup_pages' => [
                                [
                                    'id' => 283460,
                                ],
                            ],
                        ],
                    ],
                    [
                        'product' => [
                            'id' => $productId2,
                            'name' => 'Calendar Billing Product',
                            'product_family' => [
                                'id' => 527890,
                            ],
                            'public_signup_pages' => [
                                [
                                    'id' => 289193,
                                ],
                            ],
                        ],
                    ]], 200),
        ]);

        $collection = $this->getChargify()->productFamily()->listProductsForProductFamily(
            $familyId,
            [
                'page' => 1,
                'per_page' => 2,
            ]
        );

        $this->assertTrue($collection->contains('id', '=', $productId1));
        $this->assertTrue($collection->contains('id', '=', $productId2));
    }

    public function testCustomerServiceGetByChargifyIdSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $name = Str::random();
        $description = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'product_family' => [
                        'id' => $productFamilyId,
                        'name' => $name,
                        'description' => $description,
                        'handle' => $name,
                        'accounting_code' => null,
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->productFamily()->getProductFamilyById($productFamilyId);

        $this->assertEquals($productFamilyId, $response['id']);
        $this->assertEquals($name, $response['name']);
        $this->assertEquals($description, $response['description']);
    }

    public function testProductFamilyServiceCreateRequestException()
    {
        $this->expectException(RequestException::class);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'errors' => [
                        'product_family' => "can't be blank",
                    ],
                ], 422),
        ]);

        $this->getChargify()->productFamily()->create([
            'name' => Str::random(),
            'description' => Str::random(),
        ]);
    }

    public function testProductFamilyServiceCreateValidationException()
    {
        $this->expectException(ValidationException::class);

        $this->getChargify()->productFamily()->create([
            'name' => Str::random(),
        ]);
    }
}
