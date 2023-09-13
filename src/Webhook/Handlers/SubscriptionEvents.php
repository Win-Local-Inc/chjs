<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;

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
    WebhookEvents::DelayedSubscriptionCreationSuccess
)]
class SubscriptionEvents extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        ray($event, $payload);
    }
}
