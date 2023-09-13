<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;

#[HandleEvents(
    WebhookEvents::PaymentSuccess
)]
class SubscriptionPaymentUpdate extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        ray($event, $payload);
    }
}
