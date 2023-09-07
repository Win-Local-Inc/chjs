<?php

namespace Obsolete\WebhookHandlers;

use Obsolete\Attributes\HandleEvents;
use Obsolete\Enums\WebhookEvents;
use Illuminate\Support\Facades\Validator;

#[HandleEvents(
    WebhookEvents::ComponentAllocationChange
)]
class ComponentAllocation extends AbstractHandler
{
    protected function handleEvent(array $payload)
    {
        $this->validateData($payload);
        $this->getChargifySystem()
            ->updateSubscriptionComponents($payload['subscription']['id']);
    }

    protected function validateData(array &$payload): void
    {
        Validator::make($payload, [
            'subscription' => 'required|array',
            'subscription.id' => 'required|integer',
        ])->validate();
    }
}
