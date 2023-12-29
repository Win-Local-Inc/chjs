<?php

namespace WinLocalInc\Chjs\Events;

use Illuminate\Queue\SerializesModels;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;

class SubscriptionEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Subscription $subscription, public ?WebhookEvents $event = null) {}
}
