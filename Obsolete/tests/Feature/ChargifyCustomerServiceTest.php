<?php

namespace Tests\Feature;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChargifyCustomerServiceTest extends TestCase
{
    public function testCustomerServiceCreateSuccess()
    {
        $firstName = Str::random();
        $lastName = Str::random();
        $email = Str::random().'@test.com';

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'customer' => [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'id' => str_shuffle('123456789'),
                        'locale' => 'en-US',
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->customer()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ]);

        $this->assertEquals($firstName, $response['first_name']);
        $this->assertEquals($lastName, $response['last_name']);
        $this->assertEquals($email, $response['email']);
    }

    public function testCustomerServiceUpdateSuccess()
    {
        $customerId = random_int(10000000, 99999999);
        $email = Str::random().'@test.com';

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'customer' => [
                        'email' => $email,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->customer()->update($customerId, [
            'email' => $email,
        ]);

        $this->assertEquals($email, $response['email']);
    }

    public function testCustomerServiceDeleteSuccess()
    {
        $customerId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([], 204),
        ]);

        $this->getChargify()->customer()->delete($customerId);

        Http::assertSentCount(1);
    }

    public function testCustomerServiceListCustomersSuccess()
    {
        $customerId1 = random_int(10000000, 99999999);
        $customerId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'customer' => [
                        'first_name' => Str::random(),
                        'last_name' => Str::random(),
                        'id' => $customerId1,
                    ],
                ], [
                    'customer' => [
                        'first_name' => Str::random(),
                        'last_name' => Str::random(),
                        'id' => $customerId2,
                    ],
                ],
                ], 200),
        ]);

        $collection = $this->getChargify()->customer()->listCustomers([
            'page' => 1,
            'per_page' => 2,
        ]);

        $this->assertTrue($collection->contains('id', '=', $customerId1));
        $this->assertTrue($collection->contains('id', '=', $customerId2));
    }

    public function testCustomerServiceListCustomerSubscriptionsSuccess()
    {
        $customerId1 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([[
                    'id' => $customerId1,
                    'payment_collection_method' => 'automatic',
                    'customer' => [
                        'first_name' => 'string',
                    ],
                    'product' => [
                        'id' => 0,
                        'product_family' => [
                            'id' => 0,
                        ],
                        'public_signup_pages' => [[
                            'id' => 0,
                        ],
                        ],
                    ],
                    'credit_card' => [
                        'id' => 10088716,
                    ],
                    'group' => [
                        'uid' => 'string',
                    ],
                    'bank_account' => [
                        'bank_account_holder_type' => 'string',
                    ],
                    'coupons' => [[
                        'code' => 'ABCD_10',
                    ],
                    ],
                    'dunning_communication_delay_enabled' => false,
                    'dunning_communication_delay_time_zone' => 'Eastern Time (US & Canada)',
                ]], 200),
        ]);

        $collection = $this->getChargify()->customer()->listCustomerSubscriptions($customerId1);

        $this->assertTrue($collection->contains('id', '=', $customerId1));
    }

    public function testCustomerServiceGetByIdSuccess()
    {
        $customerId1 = random_int(10000000, 99999999);
        $firstName = Str::random();
        $lastName = Str::random();
        $email = Str::random().'@test.com';

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'customer' => [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'id' => $customerId1,
                        'locale' => 'en-US',
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->customer()->getCustomerById($customerId1);

        $this->assertEquals($firstName, $response['first_name']);
        $this->assertEquals($lastName, $response['last_name']);
        $this->assertEquals($email, $response['email']);
        $this->assertEquals($customerId1, $response['id']);
    }

    public function testCustomerServiceCreateRequestException()
    {
        $this->expectException(RequestException::class);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'errors' => [
                        'customer' => "can't be blank",
                    ],
                ], 422),
        ]);

        $this->getChargify()->customer()->create([
            'first_name' => Str::random(),
            'last_name' => Str::random(),
            'email' => Str::random().'@test.com',
        ]);
    }

    public function testCustomerServiceCreateValidationException()
    {
        $this->expectException(ValidationException::class);

        $this->getChargify()->customer()->create([
            'first_name' => Str::random(),
        ]);
    }
}
