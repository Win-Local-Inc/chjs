<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Events\ExpiringCardEvent;
use WinLocalInc\Chjs\Models\Subscription;

#[HandleEvents(
    WebhookEvents::ExpiringCard
)]
class ExpiringCard extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        $subscriptionId = $payload['subscription']['id'];

        $subscription = Subscription::where('subscription_id', $subscriptionId)->first();

        event(new ExpiringCardEvent($subscription, $payload));
    }
}
