<?php

namespace WinLocalInc\Chjs\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WinLocalInc\Chjs\Models\Subscription;

class RemoveFromWalletEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Subscription $subscription, int $amount, string $allocationId)
    {
    }
}
