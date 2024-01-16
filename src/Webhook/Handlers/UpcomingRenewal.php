<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Events\UpcomingRenewalEvent;
use WinLocalInc\Chjs\Models\Subscription;

#[HandleEvents(
    WebhookEvents::UpcomingRenewalNotice
)]
class UpcomingRenewal extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        $subscriptionId = $payload['subscription']['id'];

        $subscription = Subscription::where('subscription_id', $subscriptionId)->first();

        event(new UpcomingRenewalEvent($subscription, $payload));
    }
}
