<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;

class ChargifySubscriptionInvoiceAccountServiceTest extends TestCase
{
    public function testSubscriptionInvoiceAccountServiceIssueSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);
        $amountInCents = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'amount_in_cents' => $amountInCents,
                ], 200),
        ]);

        $response = $this->getChargify()->subscriptionInvoiceAccount()->issueServiceCredit($subscriptionId, [
            'amount' => $amountInCents,
            'memo' => 'jadymy na bogato',
        ]);

        $this->assertEquals($amountInCents, $response['amount_in_cents']);
    }
}
