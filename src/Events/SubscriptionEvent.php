<?php

namespace WinLocalInc\Chjs\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WinLocalInc\Chjs\Models\Subscription;

class SubscriptionEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Subscription $subscription)
    {
    }
}
