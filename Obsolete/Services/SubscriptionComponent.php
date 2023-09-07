<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class SubscriptionComponent extends AbstractService
{
    public function getSubscriptionComponent(string $subscriptionId, string $componentId): array
    {
        return $this->getClient()
            ->request('/subscriptions/'.$subscriptionId.'/components/'.$componentId, 'get')
            ->json('component', []);
    }

    public function listSubscriptionComponents(string $subscriptionId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'date_field' => 'sometimes|string|in:updated_at',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/components', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['component']);
    }

    public function listSubscriptionComponentsForSite(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'date_field' => 'sometimes|string|in:updated_at',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        return $this->getClient()
            ->request('subscriptions_components', 'get', $parameters)
            ->collect('subscriptions_components');
    }

    public function updateSubscriptionComponentsPricePoints(string $subscriptionId, array $parameters): Collection
    {
        $this->validatePayload(['parameters' => $parameters], [
            'parameters.*.component_id' => 'required',
            'parameters.*.price_point' => 'required',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/price_points', 'post', ['components' => $parameters])
            ->collect('components');
    }

    public function resetSubscriptionComponentsPricePoints(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/price_points/reset', 'post')
            ->json('subscription', []);
    }

    public function allocateComponent(string $subscriptionId, string $componentId, array $parameters): Collection
    {
        $this->validatePayload($parameters, [
            'quantity' => 'required|numeric',
            'memo' => 'sometimes|string',
            'upgrade_charge' => 'sometimes|string|in:full,prorated,none',
            'downgrade_credit' => 'sometimes|string|in:full,prorated,none',
            'accrue_charge' => 'sometimes|boolean',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/components/'.$componentId.'/allocations', 'post', ['allocation' => $parameters])
            ->collect('allocation');
    }

    public function allocateComponents(string $subscriptionId, array $parameters): Collection
    {
        $this->validatePayload($parameters, [
            'allocations' => 'required|array',
            'allocations.*.quantity' => 'required|numeric',
            'allocations.*.memo' => 'sometimes|string',
            'allocations.*.upgrade_charge' => 'sometimes|string|in:full,prorated,none',
            'allocations.*.downgrade_credit' => 'sometimes|string|in:full,prorated,none',
            'allocations.*.accrue_charge' => 'sometimes|boolean',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/allocations', 'post', $parameters)
            ->collect('allocation');
    }

    public function allocateComponentsPreview(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'allocations' => 'required|array',
            'allocations.*.quantity' => 'required|numeric',
            'allocations.*.memo' => 'sometimes|string',
            'allocations.*.upgrade_charge' => 'sometimes|string|in:full,prorated,none',
            'allocations.*.downgrade_credit' => 'sometimes|string|in:full,prorated,none',
            'allocations.*.accrue_charge' => 'sometimes|boolean',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/allocations/preview', 'post', $parameters)
            ->json();
    }

    public function listAllocations(string $subscriptionId, string $componentId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/components/'.$componentId.'/allocations', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['allocation']);
    }

    public function createUsage(string $subscriptionId, string $componentId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'quantity' => 'required|numeric',
            'price_point_id' => 'sometimes|string',
            'memo' => 'sometimes|string',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/components/'.$componentId.'/usages', 'post', ['usage' => $parameters])
            ->json('usage', []);
    }

    public function listUsages(string $subscriptionId, string $componentId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/components/'.$componentId.'/usages', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['usage']);
    }

    public function activateEventBasedComponent(string $subscriptionId, string $componentId): void
    {
        $this->getClient()
            ->request('event_based_billing/subscriptions/'.$subscriptionId.'/components/'.$componentId.'/activate', 'post');
    }

    public function deactivateEventBasedComponent(string $subscriptionId, string $componentId): void
    {
        $this->getClient()
            ->request('event_based_billing/subscriptions/'.$subscriptionId.'/components/'.$componentId.'/deactivate', 'post');
    }

    public function updatePrepaidUsageAllocationExpirationDate(string $subscriptionId, string $componentId, string $allocationId, string $expiresAt): array
    {
        $this->validatePayload(['expires_at' => $expiresAt], [
            'expires_at' => 'required|date',
        ]);

        return $this->getClient()
            ->request(
                'subscriptions/'.$subscriptionId.'/components/'.$componentId.'/allocations/'.$allocationId,
                'post',
                ['allocation' => ['expires_at' => $expiresAt]]
            )
            ->json('usage', []);
    }

    public function deletePrepaidUsageAllocation(string $subscriptionId, string $componentId, string $allocationId, string $creditScheme = 'credit'): void
    {
        $this->validatePayload(['credit_scheme' => $creditScheme], [
            'credit_scheme' => 'required|in:none,credit,refund',
        ]);

        $this->getClient()
            ->request(
                'subscriptions/'.$subscriptionId.'/components/'.$componentId.'/allocations/'.$allocationId,
                'delete',
                ['credit_scheme' => $creditScheme]
            );
    }

    public function eventIngestion(string $apiHandle, array $parameters): void
    {
        $this->getClient()
            ->request(
                $this->getConfig()->getEventsHostname().$this->getConfig()->getSubdomain().'/events/'.$apiHandle,
                'post',
                $parameters
            );
    }

    public function eventIngestionBulk(string $apiHandle, array $parameters): void
    {
        $this->getClient()
            ->request(
                $this->getConfig()->getEventsHostname().$this->getConfig()->getSubdomain().'/events/'.$apiHandle.'/bulk',
                'post',
                $parameters
            );
    }
}
