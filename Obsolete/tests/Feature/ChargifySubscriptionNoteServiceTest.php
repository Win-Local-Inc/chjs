<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;

class ChargifySubscriptionNoteServiceTest extends TestCase
{
    public function testSubscriptionNoteServiceCreateSuccess()
    {
        $noteId = random_int(10000000, 99999999);
        $subscriptionId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'note' => [
                        'id' => $noteId,
                        'body' => 'uga czaka',
                    ],
                ], 200),
        ]);

        $response = $this->getChargify()->subscriptionNote()->create($subscriptionId, [
            'body' => 'uga czaka',
            'sticky' => true,
        ]);

        $this->assertEquals($noteId, $response['id']);
    }
}
