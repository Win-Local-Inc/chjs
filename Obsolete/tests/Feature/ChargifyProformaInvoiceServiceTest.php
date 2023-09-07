<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifyProformaInvoiceServiceTest extends TestCase
{
    public function testProformaInvoiceServiceCreateSuccess()
    {
        $proformaInvoiceId = 'pro_'.Str::random();
        $subscriptionId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'uid' => $proformaInvoiceId,
                ], 200),
        ]);

        $response = $this->getChargify()->proformaInvoice()->create($subscriptionId);

        $this->assertEquals($proformaInvoiceId, $response['uid']);
    }
}
