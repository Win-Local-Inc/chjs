<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Models\SubscriptionComponent;
use WinLocalInc\Chjs\Models\SubscriptionHistory;

#[HandleEvents(
    WebhookEvents::ComponentAllocationChange
)]
class ComponentAllocation extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        // need to get unit_price and handle
        $pricePoint = maxio()->componentPrice->find($payload['price_point_id']);
        // all prices will be per_unit so only 1 element in array
        $newPrice = (int) $pricePoint->prices[0]->unit_price * (int) $payload['allocated_quantity'];

        SubscriptionHistory::createSubscriptionHistory($payload['subscription']['id'], $event);

        SubscriptionComponent::upsert(
            [[
                'subscription_id' => $payload['subscription']['id'],
                'component_id' => $payload['component']['id'],
                'component_handle' => $payload['component']['handle'],
                'component_price_handle' => $pricePoint->handle,
                'component_price_id' => $payload['price_point_id'],
                'subscription_component_quantity' => $payload['allocated_quantity'],
                'subscription_component_price' => $newPrice,
            ]],
            ['subscription_id', 'component_id']
        );
    }
}
