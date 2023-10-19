<?php

namespace WinLocalInc\Chjs\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Tests\TestCase;

class ChargifyCouponServiceTest extends TestCase
{
    public function testCouponServiceCreateSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $name = Str::random();
        $description = Str::random();
        $code = Str::upper(Str::random());
        $cents = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'coupon' => [
                        'name' => $name,
                        'description' => $description,
                        'code' => $code,
                        'amount_in_cents' => $cents,
                    ],
                ], 201),
        ]);

        $response = maxio()->coupon->create($productFamilyId, [
            'name' => $name,
            'description' => $description,
            'code' => $code,
            'amount_in_cents' => $cents,
        ]);

        $this->assertTrue($response instanceof \WinLocalInc\Chjs\Chargify\Coupon);

        $this->assertEquals($name, $response->name);
        $this->assertEquals($description, $response->description);
        $this->assertEquals($code, $response->code);
        $this->assertEquals($cents, $response->amount_in_cents);
    }

    public function testCouponServiceUpdateSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $couponId = random_int(10000000, 99999999);
        $name = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'coupon' => [
                        'name' => $name,
                    ],
                ], 200),
        ]);

        $response = maxio()->coupon->update($productFamilyId, $couponId, [
            'name' => $name,
        ]);

        $this->assertEquals($name, $response->name);
    }

    public function testCouponServiceArchiveSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $couponId = random_int(10000000, 99999999);
        $name = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'coupon' => [
                        'name' => $name,
                    ],
                ], 204),
        ]);

        $response = maxio()->coupon->archive($productFamilyId, $couponId);

        $this->assertEquals($name, $response->name);
    }

    public function testCouponServiceGetByIdSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $couponId = random_int(10000000, 99999999);
        $name = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'coupon' => [
                        'name' => $name,
                    ],
                ], 200),
        ]);

        $response = maxio()->coupon->getCouponById($productFamilyId, $couponId);

        $this->assertEquals($name, $response->name);
    }

    public function testCouponServiceGetByCodeSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $code = Str::upper(Str::random());
        $name = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'coupon' => [
                        'name' => $name,
                    ],
                ], 200),
        ]);

        $response = maxio()->coupon->getCouponByCode($productFamilyId, $code);

        $this->assertEquals($name, $response->name);
    }

    public function testCouponServiceValidateCodeSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $code = Str::upper(Str::random());
        $name = Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'coupon' => [
                        'name' => $name,
                    ],
                ], 200),
        ]);

        $response = maxio()->coupon->validateCode($productFamilyId, $code);

        $this->assertEquals($name, $response->name);
    }

    public function testCouponServiceListCouponsSuccess()
    {
        $couponId1 = random_int(10000000, 99999999);
        $couponId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'coupon' => [
                        'name' => Str::random(),
                        'id' => $couponId1,
                    ],
                ], [
                    'coupon' => [
                        'name' => Str::random(),
                        'id' => $couponId2,
                    ],
                ],
                ], 200),
        ]);

        $collection = maxio()->coupon->listCoupons([
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertTrue($collection->contains('id', '=', $couponId1));
        $this->assertTrue($collection->contains('id', '=', $couponId2));
    }

    public function testCouponServiceListCouponsForProductFamilySuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $couponId1 = random_int(10000000, 99999999);
        $couponId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'coupon' => [
                        'name' => Str::random(),
                        'id' => $couponId1,
                    ],
                ], [
                    'coupon' => [
                        'name' => Str::random(),
                        'id' => $couponId2,
                    ],
                ],
                ], 200),
        ]);

        $collection = maxio()->coupon->listCouponsForProductFamily($productFamilyId, [
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertTrue($collection->contains('id', '=', $couponId1));
        $this->assertTrue($collection->contains('id', '=', $couponId2));
    }

    public function testCouponServiceListCouponUsagesSuccess()
    {
        $productFamilyId = random_int(10000000, 99999999);
        $couponId = random_int(10000000, 99999999);
        $usageId1 = random_int(10000000, 99999999);
        $usageId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    [
                        'name' => 'No cost product',
                        'id' => $usageId1,
                        'signups' => 0,
                        'savings' => 0,
                        'savings_in_cents' => 0,
                        'revenue' => 0,
                        'revenue_in_cents' => 0,
                    ],
                    [
                        'name' => 'Trial Product',
                        'id' => $usageId2,
                        'signups' => 1,
                        'savings' => 30,
                        'savings_in_cents' => 3000,
                        'revenue' => 20,
                        'revenue_in_cents' => 2000,
                    ],
                ], 204),
        ]);

        $collection = maxio()->coupon->listCouponUsages($productFamilyId, $couponId);

        $this->assertTrue($collection->contains('id', '=', $usageId1));
        $this->assertTrue($collection->contains('id', '=', $usageId2));
    }

    public function testCouponServiceCreateUpdateCouponSubcodesSuccess()
    {
        $couponId = random_int(10000000, 99999999);
        $subcodes = [
            Str::upper(Str::random()),
            Str::upper(Str::random()),
        ];

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'created_codes' => $subcodes,
                    'duplicate_codes' => [],
                    'invalid_codes' => [],
                ], 200),
        ]);

        $response = maxio()->coupon->createUpdateCouponSubcodes($couponId, $subcodes);

        $this->assertEquals($subcodes, $response['created_codes']);
    }

    public function testCouponServiceListCouponSubcodesSuccess()
    {
        $couponId = random_int(10000000, 99999999);
        $subcodes = [
            Str::upper(Str::random()),
            Str::upper(Str::random()),
        ];
        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'codes' => $subcodes,
                ], 200),
        ]);

        $collection = maxio()->coupon->listCouponSubcodes($couponId, [
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertEquals($subcodes, $collection);
    }

    public function testCouponServiceDelteCouponSubcodeSuccess()
    {
        $couponId = random_int(10000000, 99999999);
        $subcode = Str::upper(Str::random());

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([], 204),
        ]);

        maxio()->coupon->delteCouponSubcode($couponId, $subcode);

        Http::assertSentCount(1);
    }
}
