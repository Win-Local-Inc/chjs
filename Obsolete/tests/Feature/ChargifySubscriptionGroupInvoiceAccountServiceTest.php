<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifySubscriptionGroupInvoiceAccountServiceTest extends TestCase
{
    public function testSubscriptionGroupInvoiceAccountServiceIssueSuccess()
    {
        $groupId = 'grp_'.Str::random();
        $amountInCents = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'amount_in_cents' => $amountInCents,
                ], 200),
        ]);

        $response = $this->getChargify()->subscriptionGroupInvoiceAccount()->issueServiceCredit($groupId, [
            'amount' => $amountInCents,
            'memo' => 'jadymy na bogato',
        ]);

        $this->assertEquals($amountInCents, $response['amount_in_cents']);
    }
}
