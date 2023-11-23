<?php

namespace WinLocalInc\Chjs\Services;

use WinLocalInc\Chjs\Chargify\ChargifyObject;

class SubscriptionStatusService extends AbstractService
{
    public function hold(string $subscriptionId, string $until = null): ChargifyObject
    {
        $parameters = [];

        if ($until) {
            $parameters['hold'] = ['automatically_resume_at' => $until];
        }

        return $this->post('subscriptions/'.$subscriptionId.'/hold', $parameters);
    }

    public function updateHoldAt(string $subscriptionId, string $until): ChargifyObject
    {
        return $this->put('subscriptions/'.$subscriptionId.'/hold', ['hold' => ['automatically_resume_at' => $until]]);
    }

    public function unHold(string $subscriptionId): ChargifyObject
    {
        return $this->post('subscriptions/'.$subscriptionId.'/resume');
    }

    public function retry(string $subscriptionId): ChargifyObject
    {
        return $this->put('subscriptions/'.$subscriptionId.'/retry');
    }

    public function cancelNow(string $subscriptionId): ChargifyObject
    {
        return $this->delete('subscriptions/'.$subscriptionId);
    }

    public function reactivate(string $subscriptionId, array $parameters = []): ChargifyObject
    {
        return $this->put('subscriptions/'.$subscriptionId.'/reactivate', $parameters);
    }

    public function cancel(string $subscriptionId): array
    {
        return $this->post(path: 'subscriptions/'.$subscriptionId.'/delayed_cancel', asArray: true);
    }

    public function resume(string $subscriptionId): ChargifyObject
    {
        return $this->delete('subscriptions/'.$subscriptionId.'/delayed_cancel');
    }

    public function cancelDunning(string $subscriptionId): ChargifyObject
    {
        return $this->post('subscriptions/'.$subscriptionId.'/cancel_dunning');
    }

    public function previewRenewal(string $subscriptionId, array $parameters): ChargifyObject
    {
        $this->validatePayload($parameters, [
            'components' => 'require|array',
            'components.*.component_id' => 'require|integer',
            'components.*.quantity' => 'require|integer',
            'components.*.price_point_id' => 'sometimes|integer',
        ]);

        return $this->post('subscriptions/'.$subscriptionId.'/renewals/preview', $parameters);
    }
}
