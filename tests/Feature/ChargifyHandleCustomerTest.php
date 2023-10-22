<?php

namespace WinLocalInc\Chjs\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Exceptions\InvalidCustomer;
use WinLocalInc\Chjs\Tests\Database\Models\User;
use WinLocalInc\Chjs\Tests\Database\Models\Workspace;
use WinLocalInc\Chjs\Tests\TestCase;

class ChargifyHandleCustomerTest extends TestCase
{
    public function testCreateAsChargifyCustomer()
    {
        $workspace = Workspace::factory()->create();

        $user = User::factory()
            ->workspace($workspace)
            ->create();

        $token = Str::random();
        $customerId = random_int(1000000, 9999999);
        $paymentProfileId = random_int(1000000, 9999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'customer' => [
                        'id' => $customerId,
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                        'email' => $user->email,
                    ],
                ], 200)
                ->push([
                    'payment_profile' => [
                        'id' => $paymentProfileId,
                        'customer_id' => $customerId,
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                    ],
                ], 200),
        ]);

        $user->createAsChargifyCustomer($token);

        $user->refresh();

        $this->assertEquals($user->chargify_id, $customerId);

        Http::assertSentCount(2);
    }

    public function testCustomerAsChargifyCustomerException()
    {
        $this->expectException(InvalidCustomer::class);

        $workspace = Workspace::factory()->create();

        $user = User::factory()
            ->set(
                'workspace_id',
                $workspace->workspace_id
            )
            ->create();

        $user->asChargifyCustomer();
    }

    public function testCustomerAsChargifyCustomerSuccess()
    {
        $customerId = random_int(1000000, 9999999);

        $user = User::factory()
            ->set(
                'chargify_id',
                $customerId
            )
            ->create();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'customer' => [
                        'id' => $customerId,
                        'first_name' => $user->firstname,
                        'last_name' => $user->lastname,
                        'email' => $user->email,
                    ],
                ], 200),
        ]);

        $customerObject = $user->asChargifyCustomer();

        $this->assertTrue($customerObject instanceof \WinLocalInc\Chjs\Chargify\Customer);
        $this->assertEquals($customerObject->id, $customerId);
    }
}
