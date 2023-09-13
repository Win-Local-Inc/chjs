<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\PaymentProfile;

class PaymentProfileService extends AbstractService
{
    public function create(string $customerId, string $chargifyToken): PaymentProfile
    {
        return $this->post('payment_profiles', ['payment_profile' => [
            'customer_id' => $customerId,
            'chargify_token' => $chargifyToken,
        ]]);
    }

    public function update(string $paymentProfileId, array $parameters): PaymentProfile
    {
        return $this->put('payment_profiles/'.$paymentProfileId, ['payment_profile' => $parameters]);
    }

    public function deletePaymentProfile(string $paymentProfileId): void
    {
        $this->delete('payment_profiles/'.$paymentProfileId);
    }

    public function deleteForce(string $paymentProfileId, string $subscriptionId): void
    {
        $this->delete('subscriptions/'.$subscriptionId.'/payment_profiles/'.$paymentProfileId);
    }

    public function changeSubscriptionDefaultPaymentProfile(string $paymentProfileId, string $subscriptionId): PaymentProfile
    {
        return $this->post('subscriptions/'.$subscriptionId.'/payment_profiles/'.$paymentProfileId.'/change_payment_profile');
    }

    public function changeSubscriptionGroupDefaultPaymentProfile(string $paymentProfileId, string $subscriptionGroupId): PaymentProfile
    {
        return $this->post('subscription_groups/'.$subscriptionGroupId.'/payment_profiles/'.$paymentProfileId.'/change_payment_profile');
    }

    public function deleteSubscriptionGroupPaymentProfile(string $paymentProfileId, string $subscriptionGroupId): void
    {
        $this->delete('subscription_groups/'.$subscriptionGroupId.'/payment_profiles/'.$paymentProfileId);
    }

    public function getPaymentProfileById(string $paymentProfileId): PaymentProfile
    {
        return $this->get('payment_profiles/'.$paymentProfileId);
    }

    public function getByToken(string $chargifyToken): PaymentProfile
    {
        return $this->get('one_time_tokens/'.$chargifyToken);
    }

    public function sendRequestPaymentUpdateEmail(string $subscriptionId): void
    {
        $this->post('subscriptions/'.$subscriptionId.'/request_payment_profiles_update');
    }

    public function list(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'customer_id' => 'sometimes|integer',
        ]);

        return $this->get('payment_profiles', $parameters);
    }
}
