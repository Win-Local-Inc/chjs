<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Chargify\PricePoints;
use WinLocalInc\Chjs\Enums\ProductPricing;
use WinLocalInc\Chjs\Enums\SubscriptionInterval;
use WinLocalInc\Chjs\Enums\SubscriptionStatus;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Events\SubscriptionEvent;
use WinLocalInc\Chjs\Events\TopUpWalletEvent;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;
use WinLocalInc\Chjs\Models\SubscriptionHistory;
use WinLocalInc\Chjs\ProductStructure;
use WinLocalInc\Chjs\Webhook\ChargifyUtility;

#[HandleEvents(
    WebhookEvents::PaymentSuccess,
    WebhookEvents::SignupSuccess,
    WebhookEvents::RenewalSuccess,
    WebhookEvents::PaymentFailure,
    WebhookEvents::RenewalFailure,
    WebhookEvents::DunningStepReached,
    WebhookEvents::BillingDateChange,
    WebhookEvents::SubscriptionStateChange,
    WebhookEvents::DelayedSubscriptionCreationSuccess,
    WebhookEvents::UpgradeDowngradeSuccess,
    WebhookEvents::UpgradeDowngradeFailure,
    WebhookEvents::PendingCancellationChange,
    WebhookEvents::SubscriptionProductChange
)]
class SubscriptionEvents extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        $data = $payload['subscription'];

        if($data['product']['product_price_point_handle'] == ProductPricing::AD_RECURRING->value) {
            return;
        }

        SubscriptionHistory::createSubscriptionHistory($data['id'], $event);

        $subscriptionData = [
            'subscription_id' => $data['id'],
            'user_id' => $data['customer']['reference'],
            'workspace_id' => $data['reference'],
            'product_price_handle' => $data['product']['product_price_point_handle'],
            'product_handle' => $data['product']['handle'],
            'payment_collection_method' => $data['payment_collection_method'],
            'subscription_interval' => SubscriptionInterval::getIntervalUnit((int) $data['product']['interval'])->value,
            'total_revenue_in_cents' => $data['total_revenue_in_cents'],
            'product_price_in_cents' => $data['product_price_in_cents'],
            'next_billing_at' => ChargifyUtility::getFixedDateTime($data['next_assessment_at']),
            'ends_at' => $this->getEndsAt($data),
        ];

        if ($event === WebhookEvents::SubscriptionStateChange->value) {
            $subscriptionData['status'] = $this->getStatus($data);
        }

        Subscription::upsert([$subscriptionData], ['workspace_id']);

        $this->updateComponents($data['id'], $data['product']['handle']);

        $subscription = Subscription::where('subscription_id', $data['id'])->first();

        event(new SubscriptionEvent($subscription, WebhookEvents::from($event), $payload));
    }

    protected function getEndsAt(array $data)
    {
        if ($data['state'] === SubscriptionStatus::Canceled->value) {
            return ChargifyUtility::getFixedDateTime($data['canceled_at']);
        }

        return ChargifyUtility::getFixedDateTime($data['scheduled_cancellation_at']);
    }

    protected function getStatus(array $data)
    {
        if ($data['state'] === SubscriptionStatus::Active->value && $data['scheduled_cancellation_at'] !== null) {
            return SubscriptionStatus::OnGracePeriod->value;
        }

        return $data['state'];
    }

    protected function updateComponents(string $subscriptionId, string $productHandle)
    {
        if (! in_array($this->event, [
            WebhookEvents::SignupSuccess->value,
            WebhookEvents::RenewalSuccess->value,
            WebhookEvents::SubscriptionStateChange->value,
            WebhookEvents::SubscriptionProductChange->value,
            WebhookEvents::DelayedSubscriptionCreationSuccess->value,
        ])) {
            return;
        }

        $componentsResponse = maxio()->subscriptionComponent->list($subscriptionId);

        $componentPrices = maxio()->componentPrice->list(['filter' => [
            'ids' => implode(',', $componentsResponse->pluck('price_point_id')->toArray()),
            'type' => 'catalog,default,custom',
        ]]);

        $pricesMap = $componentPrices->reduce(function (array $carry, PricePoints $item) {
            $carry[$item->id] = $item->prices->first()->unit_price;

            return $carry;
        }, []);

        $upsertComponents = $componentsResponse->map(function ($component) use (&$pricesMap, $subscriptionId) {
            return [
                'subscription_id' => $subscriptionId,
                'component_id' => $component->component_id,
                'component_handle' => $component->component_handle,
                'component_price_handle' => $component->price_point_handle,
                'component_price_id' => $component->price_point_id,
                'subscription_component_price' => $pricesMap[$component->price_point_id],
                'subscription_component_quantity' => $component->allocated_quantity,
                'created_at' => ChargifyUtility::getFixedDateTime($component->created_at),
                'updated_at' => ChargifyUtility::getFixedDateTime($component->updated_at),
            ];
        })
            ->toArray();

        DB::transaction(function () use ($subscriptionId, $upsertComponents) {
            SubscriptionComponent::where('subscription_id', $subscriptionId)->delete();
            SubscriptionComponent::upsert($upsertComponents, ['subscription_id', 'component_id']);
        });

        $subscription = Subscription::where('subscription_id', $subscriptionId)->first();
        ProductStructure::setMainComponent(subscription: $subscription);

        $this->walletUpdate($subscription, $componentsResponse, $componentPrices);
    }

    protected function walletUpdate(Subscription $subscription, Collection $components, Collection $componentPrices): void
    {
        if (! in_array($this->event, [
            WebhookEvents::SignupSuccess->value,
            WebhookEvents::RenewalSuccess->value,
        ])) {
            return;
        }

        $adCredit = $components->filter(function ($item) {
            return $item->component_handle === 'ad_credit';
        })->first();

        $adCreditPrice = $componentPrices->filter(function ($item) {
            return $item->handle === 'ad_credit';
        })->first();

        if ($adCredit && $adCreditPrice) {
            $unitPrice = $adCreditPrice->prices->first()->unit_price;
            $quantity = $adCredit->allocated_quantity;
            event(new TopUpWalletEvent($subscription, $unitPrice * $quantity, $this->payload['event_id']));
        }
    }
}
