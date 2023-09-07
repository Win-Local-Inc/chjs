<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class PaymentProfile extends AbstractService
{
    public function create(string $customerId, string $chargifyToken): array
    {
        return $this->getClient()
            ->request('payment_profiles', 'post', ['payment_profile' => [
                'customer_id' => $customerId,
                'chargify_token' => $chargifyToken,
            ]])
            ->json('payment_profile', []);
    }

    public function update(string $paymentProfileId, array $parameters): array
    {
        return $this->getClient()
            ->request('payment_profiles/'.$paymentProfileId, 'put', ['payment_profile' => $parameters])
            ->json('payment_profile', []);
    }

    public function delete(string $paymentProfileId): void
    {
        $this->getClient()
            ->request('payment_profiles/'.$paymentProfileId, 'delete');
    }

    public function deleteForce(string $paymentProfileId, string $subscriptionId): void
    {
        $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/payment_profiles/'.$paymentProfileId, 'delete');
    }

    public function changeSubscriptionDefaultPaymentProfile(string $paymentProfileId, string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/payment_profiles/'.$paymentProfileId.'/change_payment_profile', 'post')
            ->json('payment_profile', []);
    }

    public function changeSubscriptionGroupDefaultPaymentProfile(string $paymentProfileId, string $subscriptionGroupId): array
    {
        return $this->getClient()
            ->request('subscription_groups/'.$subscriptionGroupId.'/payment_profiles/'.$paymentProfileId.'/change_payment_profile', 'post')
            ->json('payment_profile', []);
    }

    public function deleteSubscriptionGroupPaymentProfile(string $paymentProfileId, string $subscriptionGroupId): void
    {
        $this->getClient()
            ->request('subscription_groups/'.$subscriptionGroupId.'/payment_profiles/'.$paymentProfileId, 'delete');
    }

    public function getPaymentProfileById(string $paymentProfileId): array
    {
        return $this->getClient()
            ->request('payment_profiles/'.$paymentProfileId, 'get')
            ->json('payment_profile', []);
    }

    public function getPaymentProfileByToken(string $chargifyToken): array
    {
        return $this->getClient()
            ->request('one_time_tokens/'.$chargifyToken, 'get')
            ->json('payment_profile', []);
    }

    public function sendRequestPaymentUpdateEmail(string $subscriptionId): void
    {
        $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/request_payment_profiles_update', 'post');
    }

    public function listPaymentProfiles(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'customer_id' => 'sometimes|integer',
        ]);

        return $this->getClient()
            ->request('payment_profiles', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['payment_profile']);
    }
}
