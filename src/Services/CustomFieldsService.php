<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;

class CustomFieldsService extends AbstractService
{
    public function createMedadata(string $resourceId, string $resourceType, array $metadata): array
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->validatePayload(['metadata' => $metadata], [
            'metadata.*.name' => 'required|string',
            'metadata.*.value' => 'required|string',
        ]);

        return $this->post($resourceType . '/' . $resourceId . '/metadata', ['metadata' => $metadata], true);
    }

    public function updateMedadata(string $resourceId, string $resourceType, array $metadata): array
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->validatePayload(['metadata' => $metadata], [
            'metadata.*.current_name' => 'required|string',
            'metadata.*.name' => 'required|string',
            'metadata.*.value' => 'required|string',
        ]);

        return $this->put($resourceType . '/' . $resourceId . '/metadata', ['metadata' => $metadata], true);
    }

    public function deleteMedadata(string $resourceId, string $resourceType, array $names): void
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->delete($resourceType . '/' . $resourceId . '/metadata', ['names' => $names]);
    }

    public function listMetadata(string $resourceId, string $resourceType = 'subscriptions', array $parameters = []): Collection
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->get($resourceType . '/' . $resourceId . '/metadata', $parameters);
    }

    public function createMedafields(string $resourceType, array $metafields): Collection
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->validatePayload(['metafields' => $metafields], [
            'metafields.*.name' => 'required|string',
        ]);

        return $this->get($resourceType . '/metafields', ['metafields' => $metafields]);
    }

    public function updateMedafields(string $resourceType, array $metafields): Collection
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->validatePayload(['metafields' => $metafields], [
            'metafields.*.current_name' => 'required|string',
            'metafields.*.name' => 'required|string',
        ]);

        return $this->put($resourceType . '/metafields', ['metafields' => $metafields]);
    }

    public function deleteMedafield(string $resourceType, string $name): void
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->delete($resourceType . '/metafields', ['name' => $name]);
    }

    public function listMetafields(string $resourceType, array $parameters = []): Collection
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->get($resourceType . '/metafields', $parameters);
    }
}
