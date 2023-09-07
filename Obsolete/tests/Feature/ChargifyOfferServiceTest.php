<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifyOfferServiceTest extends TestCase
{
    public function testOfferServiceCreateSuccess()
    {
        $name = Str::random();
        $handle = Str::lower(Str::random());
        $productId = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push([
                    'offer' => [
                        'name' => $name,
                        'handle' => $handle,
                        'product_id' => $productId,
                    ],
                ], 201),
        ]);

        $response = $this->getChargify()->offer()->create([
            'name' => $name,
            'handle' => $handle,
            'product_id' => $productId,
        ]);

        $this->assertEquals($name, $response['name']);
        $this->assertEquals($handle, $response['handle']);
        $this->assertEquals($productId, $response['product_id']);
    }

    public function testOfferServiceListOffersSuccess()
    {
        $productId1 = random_int(10000000, 99999999);
        $productId2 = random_int(10000000, 99999999);

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push(['offers' => [[
                    'product_id' => $productId1,
                ], [
                    'product_id' => $productId2,
                ],
                ]], 200),
        ]);

        $collection = $this->getChargify()->offer()->listOffers();

        $this->assertTrue($collection->contains('product_id', '=', $productId1));
        $this->assertTrue($collection->contains('product_id', '=', $productId2));
    }
}
