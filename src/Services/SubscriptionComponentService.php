<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;

class SubscriptionComponentService extends AbstractService
{
    public function find(string $subscriptionId, string $componentId): ChargifyObject
    {
        return $this->get('/subscriptions/'.$subscriptionId.'/components/'.$componentId);
    }

    public function list(string $subscriptionId, array $options = []): Collection
    {
        $this->validatePayload($options, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'date_field' => 'sometimes|string|in:updated_at',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        $parameters = array_merge([
            'price_point_ids' => 'not_null',
        ], $options);

        return $this->get('subscriptions/'.$subscriptionId.'/components', $parameters);
    }

    public function listForSite(array $options = []): Collection
    {
        $this->validatePayload($options, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'date_field' => 'sometimes|string|in:updated_at',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        $parameters = array_merge([
            'price_point_ids' => 'not_null',
        ], $options);

        return $this->get('subscriptions_components', $parameters);
    }

    //dont use
    public function reset(string $subscriptionId): ChargifyObject
    {
        return $this->post('subscriptions/'.$subscriptionId.'/price_points/reset');
    }

    public function updateQuantity(string $subscriptionId, string $componentId, array $options = []): ChargifyObject
    {
        $allocation = array_merge([
            'upgrade_charge' => 'full',
            'downgrade_credit' => 'full',
            'accrue_charge' => false,
        ], $options);

        return $this->post('subscriptions/'.$subscriptionId.'/components/'.$componentId.'/allocations', ['allocation' => $allocation]);
    }

    public function updateQuantityBulk(string $subscriptionId, array $parameters): Collection
    {
        $this->validatePayload($parameters, [
            'allocations' => 'required|array',
            'allocations.*.quantity' => 'required|numeric',
            'allocations.*.memo' => 'sometimes|string',
            'allocations.*.upgrade_charge' => 'sometimes|string|in:full,prorated,none',
            'allocations.*.downgrade_credit' => 'sometimes|string|in:full,prorated,none',
            'allocations.*.accrue_charge' => 'sometimes|boolean',
        ]);

        return $this->post('subscriptions/'.$subscriptionId.'/allocations', $parameters);
    }

    public function allocateComponentsPreview(string $subscriptionId, string $componentId, string $pricePoint, int $qty): ChargifyObject
    {
        $allocation = ['allocations' => [
            'component_id' => $componentId,
            'quantity' => $qty,
            'upgrade_charge' => 'full',
            'downgrade_credit' => 'full',
            'accrue_charge' => false,
            'price_point_id' => 'handle:'.$pricePoint,
        ]];

        return $this
            ->post('subscriptions/'.$subscriptionId.'/allocations/preview', $allocation);
    }

    public function listAllocations(string $subscriptionId, string $componentId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
        ]);

        return $this
            ->get('subscriptions/'.$subscriptionId.'/components/'.$componentId.'/allocations', $parameters)
            ->collect()
            ->map(fn ($item) => $item['allocation']);
    }

    public function createUsage(string $subscriptionId, string $componentId, int $qty): ChargifyObject
    {
        return $this
            ->post('subscriptions/'.$subscriptionId.'/components/'.$componentId.'/usages', ['usage' => ['quantity' => $qty]]);
    }

    public function listUsages(string $subscriptionId, string $componentId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this
            ->get('subscriptions/'.$subscriptionId.'/components/'.$componentId.'/usages', $parameters);
    }

    public function activateEventBasedComponent(string $subscriptionId, string $componentId): void
    {
        $this->post('event_based_billing/subscriptions/'.$subscriptionId.'/components/'.$componentId.'/activate');
    }

    public function deactivateEventBasedComponent(string $subscriptionId, string $componentId): void
    {
        $this->post('event_based_billing/subscriptions/'.$subscriptionId.'/components/'.$componentId.'/deactivate');
    }

    public function updatePrepaidUsageAllocationExpirationDate(string $subscriptionId, string $componentId, string $allocationId, string $expiresAt): object
    {
        $this->validatePayload(['expires_at' => $expiresAt], [
            'expires_at' => 'required|date',
        ]);

        return $this
            ->post('subscriptions/'.$subscriptionId.'/components/'.$componentId.'/allocations/'.$allocationId, ['allocation' => ['expires_at' => $expiresAt]]);
    }

    public function deletePrepaidUsageAllocation(string $subscriptionId, string $componentId, string $allocationId, string $creditScheme = 'credit'): void
    {
        $this->validatePayload(['credit_scheme' => $creditScheme], [
            'credit_scheme' => 'required|in:none,credit,refund',
        ]);

        $this->delete('subscriptions/'.$subscriptionId.'/components/'.$componentId.'/allocations/'.$allocationId, ['credit_scheme' => $creditScheme]);
    }

    public function eventIngestion(string $apiHandle, array $parameters): void
    {
        $this->post(config('chjs.subdomain').$this->getConfig()->getSubdomain().'/events/'.$apiHandle, $parameters);
    }

    public function eventIngestionBulk(string $apiHandle, array $parameters): void
    {
        $this->post(config('chjs.event_host').config('chjs.subdomain').'/events/'.$apiHandle.'/bulk', $parameters);
    }
}
