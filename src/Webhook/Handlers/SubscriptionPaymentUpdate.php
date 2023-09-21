<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\PaymentCollectionMethod;
use WinLocalInc\Chjs\Enums\WebhookEvents;

#[HandleEvents(
    WebhookEvents::PaymentSuccess
)]
class SubscriptionPaymentUpdate extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        $subscription = $payload['subscription'];
        $this->updatePaymentProfile($subscription);
        $this->updatePaymentCollectionMethod($subscription);
    }

    protected function updatePaymentProfile(array &$subscription)
    {
        if ($this->isPaymentProfileSetUp($subscription)) {
            return;
        }

        $responseProfiles = maxio()->paymentProfile->list(['customer_id' => $subscription['customer']['id']]);
        maxio()->paymentProfile->setDefault($responseProfiles[0]->id, $subscription['id']);
    }

    protected function updatePaymentCollectionMethod(array &$subscription)
    {
        if ($this->isPaymentCollectionAutomatic($subscription)) {
            return;
        }

        maxio()->subscription->update($subscription['id'], [
            'payment_collection_method' => PaymentCollectionMethod::Automatic->value,
        ]);
    }

    protected function isPaymentProfileSetUp(array &$subscription): bool
    {
        return array_key_exists('credit_card', $subscription) &&
            array_key_exists('id', $subscription['credit_card']);
    }

    protected function isPaymentCollectionAutomatic(array &$subscription): bool
    {
        return $subscription['payment_collection_method'] === PaymentCollectionMethod::Automatic->value;
    }
}
