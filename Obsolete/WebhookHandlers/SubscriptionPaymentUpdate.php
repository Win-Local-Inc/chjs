<?php

namespace Obsolete\WebhookHandlers;

use Obsolete\Attributes\HandleEvents;
use Obsolete\ChargifyFacade;
use Obsolete\Enums\SubscriptionPaymentCollectionMethod;
use Obsolete\Enums\WebhookEvents;
use Illuminate\Support\Facades\Validator;

#[HandleEvents(
    WebhookEvents::PaymentSuccess
)]
class SubscriptionPaymentUpdate extends AbstractHandler
{
    protected function handleEvent(array $payload)
    {
        $this->validateData($payload);
        $subscription = $payload['subscription'];
        $this->updatePaymentProfile($subscription);
        $this->updatePaymentCollectionMethod($subscription);
    }

    protected function validateData(array &$payload): void
    {
        Validator::make($payload, [
            'subscription' => 'required|array',
            'subscription.id' => 'required|integer',
            'subscription.customer.id' => 'required|integer',
            'subscription.payment_collection_method' => 'required|string',
        ])->validate();
    }

    protected function updatePaymentProfile(array &$subscription)
    {
        if ($this->isPaymentProfileSetUp($subscription)) {
            return;
        }

        $responseProfiles = ChargifyFacade::paymentProfile()
            ->listPaymentProfiles(['customer_id' => $subscription['customer']['id']]);
        $paymentProfile = $responseProfiles[0];

        ChargifyFacade::paymentProfile()
            ->changeSubscriptionDefaultPaymentProfile($paymentProfile['id'], $subscription['id']);
    }

    protected function updatePaymentCollectionMethod(array &$subscription)
    {
        if ($this->isPaymentCollectionAutomatic($subscription)) {
            return;
        }

        ChargifyFacade::subscription()->update($subscription['id'], [
            'payment_collection_method' => SubscriptionPaymentCollectionMethod::Automatic->value,
        ]);
    }

    protected function isPaymentProfileSetUp(array &$subscription): bool
    {
        return array_key_exists('credit_card', $subscription) &&
            array_key_exists('id', $subscription['credit_card']);
    }

    protected function isPaymentCollectionAutomatic(array &$subscription): bool
    {
        return $subscription['payment_collection_method'] === SubscriptionPaymentCollectionMethod::Automatic->value;
    }
}
