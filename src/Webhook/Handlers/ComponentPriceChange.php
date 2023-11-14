<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Events\SubscriptionEvent;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;
use WinLocalInc\Chjs\Models\SubscriptionHistory;

#[HandleEvents(
    WebhookEvents::ItemPricePointChanged
)]
class ComponentPriceChange extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        $componentId = $payload['item_id'];
        $subscriptionId = $payload['subscription_id'];
        $pricePointId = $payload['current_price_point']['id'];

        $component = maxio()->subscriptionComponent->find($subscriptionId, $componentId);
        $pricePoint = maxio()->componentPrice->find($pricePointId)->price_point;

        $newPrice = (int) $pricePoint->prices[0]->unit_price * (int) $component->allocated_quantity;

        SubscriptionHistory::createSubscriptionHistory($subscriptionId, $event);

        SubscriptionComponent::upsert(
            [[
                'subscription_id' => $subscriptionId,
                'component_id' => $componentId,
                'component_handle' => $payload['item_handle'],
                'component_price_handle' => $pricePoint->handle,
                'component_price_id' => $pricePointId,
                'subscription_component_quantity' => (int) $component->allocated_quantity,
                'subscription_component_price' => $newPrice,
            ]],
            ['subscription_id', 'component_id']
        );

        $subscription = Subscription::where('subscription_id', $subscriptionId)->first();
        event(new SubscriptionEvent($subscription));
    }
}
