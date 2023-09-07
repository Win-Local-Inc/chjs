<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifyInvoiceServiceTest extends TestCase
{
    public function testInvoiceServiceCreateInvoiceSuccess()
    {
        $subscriptionId = random_int(10000000, 99999999);
        $invoiceId = 'inv_'.Str::random();
        $title = Str::random();
        $quantity = random_int(1, 10);
        $unitPrice = random_int(1, 10);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'invoice' => [
                        'uid' => $invoiceId,
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->invoice()->createInvoice($subscriptionId, [
            'line_items' => [
                [
                    'title' => $title,
                    'quantity' => $quantity,
                    'unitPrice' => $unitPrice,
                ],
            ],
            'coupons' => [
                [
                    'code' => 'COUPONCODE',
                    'percentage' => 50.0,
                ],
            ],
        ]);

        $this->assertEquals($invoiceId, $response['uid']);
    }

    public function testInvoiceServiceCreatePaymentSuccess()
    {
        $invoiceId = 'inv_'.Str::random();
        $amount = random_int(1, 10);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'uid' => $invoiceId,
                ], 200),
        ]);

        $response = $this->getChargify()->invoice()->createPayment($invoiceId, ['amount' => $amount]);

        $this->assertEquals($invoiceId, $response['uid']);
    }
}
