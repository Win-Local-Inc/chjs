<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;

#[HandleEvents(
    WebhookEvents::CustomerCreate,
    WebhookEvents::CustomerUpdate
)]
class CustomerUpsert extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        ray($event, $payload);
    }
}
