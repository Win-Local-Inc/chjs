<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChargifySubscriptionGroupStatusServiceTest extends TestCase
{
    public function testSubscriptionStatusServiceCancelSuccess()
    {
        $groupId1 = 'grp_'.Str::random();

        Http::fake([
            'chargify.test/*' => Http::sequence()
                ->push('', 200),
        ]);

        $this->getChargify()->subscriptionGroupStatus()->initiateDelayedGroupCancellation($groupId1);

        Http::assertSentCount(1);
    }
}
