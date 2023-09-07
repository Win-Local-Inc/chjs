<?php

namespace Obsolete\Services;

class SubscriptionGroupStatus extends AbstractService
{
    public function cancelGroupedSubscriptions(string $groupId, array $parameters = []): void
    {
        $this->validatePayload($parameters, [
            'charge_unbilled_usage' => 'sometimes|boolean',
        ]);

        $this->getClient()
            ->request('subscription_groups/'.$groupId.'/cancel', 'post', $parameters);
    }

    public function reactivateSubscriptionGroup(string $groupId, array $parameters = []): array
    {
        $this->validatePayload($parameters, [
            'resume' => 'sometimes|boolean',
            'resume_members' => 'sometimes|boolean',
        ]);

        return $this->getClient()
            ->request('subscription_groups/'.$groupId.'/reactivate', 'post', $parameters)
            ->json();
    }

    public function initiateDelayedGroupCancellation(string $groupId): void
    {
        $this->getClient()
            ->request('subscription_groups/'.$groupId.'/delayed_cancel', 'post');
    }

    public function cancelDelayedGroupCancellation(string $groupId): void
    {
        $this->getClient()
            ->request('subscription_groups/'.$groupId.'/delayed_cancel', 'delete');
    }
}
