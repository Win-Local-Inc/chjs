<?php

namespace Obsolete\Services;

class SubscriptionStatus extends AbstractService
{
    public function pauseSubscription(string $subscriptionId, array $parameters = []): array
    {
        $this->validatePayload($parameters, [
            'automatically_resume_at' => 'sometimes|date',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/hold', 'post', ! empty($parameters) ? ['hold' => $parameters] : [])
            ->json('subscription', []);
    }

    public function updateAutomaticSubscriptionResumption(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'automatically_resume_at' => 'nullable|date',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/hold', 'put', ['hold' => $parameters])
            ->json('subscription', []);
    }

    public function resumeSubscription(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/resume', 'post')
            ->json('subscription', []);
    }

    public function retrySubscription(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/retry', 'put')
            ->json('subscription', []);
    }

    public function cancelSubscription(string $subscriptionId, array $parameters = []): array
    {
        $this->validatePayload($parameters, [
            'cancellation_message' => 'sometimes|string',
            'reason_code' => 'sometimes|string',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId, 'delete', ! empty($parameters) ? ['subscription' => $parameters] : [])
            ->json('subscription', []);
    }

    public function reactivateSubscription(string $subscriptionId, array $parameters = []): array
    {
        $this->validatePayload($parameters, [
            'automatically_resume_at' => 'sometimes|date',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/reactivate', 'put', $parameters)
            ->json('subscription', []);
    }

    public function initiateDelayedCancellation(string $subscriptionId, array $parameters = []): array
    {
        $this->validatePayload($parameters, [
            'cancellation_message' => 'sometimes|string',
            'reason_code' => 'sometimes|string',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/delayed_cancel', 'post', ! empty($parameters) ? ['subscription' => $parameters] : [])
            ->json();
    }

    public function cancelDelayedCancellation(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/delayed_cancel', 'delete')
            ->json();
    }

    public function cancelDunning(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/cancel_dunning', 'post')
            ->json('subscription', []);
    }

    public function previewRenewal(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'components' => 'require|array',
            'components.*.component_id' => 'require|integer',
            'components.*.quantity' => 'require|integer',
            'components.*.price_point_id' => 'sometimes|integer',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/renewals/preview', 'post', $parameters)
            ->json();
    }
}
