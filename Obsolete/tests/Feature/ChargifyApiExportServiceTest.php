<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;

class ChargifyApiExportServiceTest extends TestCase
{
    public function testApiExportServiceCreateInvoicesSuccess()
    {
        $batchId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'batchjob' => [
                        'id' => $batchId,
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->apiExport()->createInvoicesExport();

        $this->assertEquals($batchId, $response['id']);
    }
}
