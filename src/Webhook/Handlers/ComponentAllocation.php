<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;

#[HandleEvents(
    WebhookEvents::ComponentAllocationChange
)]
class ComponentAllocation extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        ray($event, $payload);
    }
}
