<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class SubscriptionGroup extends AbstractService
{
    public function signup(array $parameters): array
    {
        $this->validatePayload($parameters, [
            'payer_id' => 'required|integer',
            'payment_profile_id' => 'required|integer',
            'subscriptions' => 'required|array',
        ]);

        return $this->getClient()
            ->request('subscription_groups/signup', 'post', ['subscription_group' => $parameters])
            ->json();
    }

    public function create(array $parameters): array
    {
        $this->validatePayload($parameters, [
            'subscription_id' => 'required|integer',
            'member_ids' => 'present|array',
        ]);

        return $this->getClient()
            ->request('subscription_groups', 'post', ['subscription_group' => $parameters])
            ->json('subscription_group', []);
    }

    public function update(string $groupId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'member_ids' => 'present|array',
        ]);

        return $this->getClient()
            ->request('subscription_groups/'.$groupId, 'put', ['subscription_group' => $parameters])
            ->json('subscription_group', []);
    }

    public function delete(string $groupId): array
    {
        return $this->getClient()
            ->request('subscription_groups/'.$groupId, 'delete')
            ->json();
    }

    public function addSubscriptionToGroup(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'target' => 'required|array',
            'billing' => 'sometimes|array',
        ]);

        return $this->getClient()
            ->request('/subscriptions/'.$subscriptionId.'/group', 'post', ['group' => $parameters])
            ->json('subscription_group', []);
    }

    public function removeSubscriptionFromGroup(string $subscriptionId): void
    {
        $this->getClient()
            ->request('/subscriptions/'.$subscriptionId.'/group', 'delete');
    }

    public function list(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->getClient()
            ->request('subscription_groups', 'get', $parameters)
            ->collect('subscription_groups');
    }

    public function getGroupById(string $groupId): array
    {
        return $this->getClient()
            ->request('subscription_groups/'.$groupId, 'get')
            ->json();
    }

    public function getGroupBySubscriptionId(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscription_groups/lookup', 'get', ['subscription_id' => $subscriptionId])
            ->json();
    }
}
