<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\SubscriptionInterval;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Events\SubscriptionEvent;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionHistory;
use WinLocalInc\Chjs\Webhook\ChargifyUtility;

#[HandleEvents(
    WebhookEvents::PaymentSuccess,
    WebhookEvents::SignupSuccess,
    WebhookEvents::RenewalSuccess,
    WebhookEvents::PaymentFailure,
    WebhookEvents::SignupFailure,
    WebhookEvents::RenewalFailure,
    WebhookEvents::DunningStepReached,
    WebhookEvents::BillingDateChange,
    WebhookEvents::SubscriptionStateChange,
    WebhookEvents::DelayedSubscriptionCreationSuccess,
    WebhookEvents::UpgradeDowngradeSuccess,
    WebhookEvents::UpgradeDowngradeFailure,
)]
class SubscriptionEvents extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        $data = $payload['subscription'];

        SubscriptionHistory::createSubscriptionHistory($data['id'], $event);

        Subscription::upsert([[
            'subscription_id' => $data['id'],
            'user_id' => $data['customer']['reference'],
            'workspace_id' => $data['reference'],
            'product_price_handle' => $data['product']['product_price_point_handle'],
            'product_handle' => $data['product']['handle'],
            'status' => $data['state'],
            'payment_collection_method' => $data['payment_collection_method'],
            'subscription_interval' => SubscriptionInterval::getIntervalUnit((int) $data['product']['interval'])->value,
            'total_revenue_in_cents' => $data['total_revenue_in_cents'],
            'next_billing_at' => ChargifyUtility::getFixedDateTime($data['next_assessment_at']),
            'ends_at' => ChargifyUtility::getFixedDateTime($data['scheduled_cancellation_at']),
        ]], ['workspace_id']);

        $subscription = Subscription::where('subscription_id', $data['id'])->first();

        event(new SubscriptionEvent($subscription));
    }
}
