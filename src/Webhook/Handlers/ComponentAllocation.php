<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Events\RemoveFromWalletEvent;
use WinLocalInc\Chjs\Events\TopUpWalletEvent;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;
use WinLocalInc\Chjs\Models\SubscriptionHistory;
use WinLocalInc\Chjs\ProductStructure;

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
        $unitPrice = (int) $pricePoint->prices[0]->unit_price;
        $newPrice = $unitPrice * (int) $payload['new_allocation'];

        $subscriptionId = $payload['subscription']['id'];

        SubscriptionHistory::createSubscriptionHistory($subscriptionId, $event);

        SubscriptionComponent::upsert(
            [[
                'subscription_id' => $subscriptionId,
                'component_id' => $payload['component']['id'],
                'component_handle' => $payload['component']['handle'],
                'component_price_handle' => $pricePoint->handle,
                'component_price_id' => $payload['price_point_id'],
                'subscription_component_quantity' => $payload['new_allocation'],
                'subscription_component_price' => $newPrice,
            ]],
            ['subscription_id', 'component_id']
        );

        $subscription = Subscription::where('subscription_id', $subscriptionId)->first();
        ProductStructure::setMainComponent(subscription: $subscription);

        $this->walletUpdate($subscription, $payload, $unitPrice);
    }

    protected function walletUpdate(Subscription $subscription, array &$payload, int $unitPrice): void
    {
        if (in_array($payload['component']['handle'], ['ad_credit', 'ad_credit_one_time'])) {

            if (array_key_exists('payment', $payload) && is_array($payload['payment']) && $payload['payment']['success']) {
                event(new TopUpWalletEvent($subscription, $payload['payment']['amount_in_cents'], $payload['payment']['id']));

                return;
            }

            if (array_key_exists('payment', $payload) && ! is_array($payload['payment'])) {
                $quantity = (int) $payload['previous_allocation'] - (int) $payload['new_allocation'];
                if ($quantity > 0) {
                    event(new RemoveFromWalletEvent($subscription, $quantity * $unitPrice, $payload['allocation']['id']));

                    return;
                }
            }
        }
    }
}
