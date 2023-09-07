<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class CustomFields extends AbstractService
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

        return $this->getClient()
            ->request($resourceType.'/'.$resourceId.'/metadata', 'post', ['metadata' => $metadata])
            ->json();
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

        return $this->getClient()
            ->request($resourceType.'/'.$resourceId.'/metadata', 'put', ['metadata' => $metadata])
            ->json();
    }

    public function deleteMedadata(string $resourceId, string $resourceType, array $names): void
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->getClient()
            ->request($resourceType.'/'.$resourceId.'/metadata', 'delete', ['names' => $names]);
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

        return $this->getClient()
            ->request($resourceType.'/'.$resourceId.'/metadata', 'get', $parameters)
            ->collect('metadata');
    }

    public function createMedafields(string $resourceType, array $metafields): Collection
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->validatePayload(['metafields' => $metafields], [
            'metafields.*.name' => 'required|string',
        ]);

        return $this->getClient()
            ->request($resourceType.'/metafields', 'post', ['metafields' => $metafields])
            ->collect();
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

        return $this->getClient()
            ->request($resourceType.'/metafields', 'put', ['metafields' => $metafields])
            ->collect();
    }

    public function deleteMedafield(string $resourceType, string $name): void
    {
        $this->validatePayload(['resourceType' => $resourceType], [
            'resourceType' => 'required|in:customers,subscriptions',
        ]);

        $this->getClient()
            ->request($resourceType.'/metafields', 'delete', ['name' => $name]);
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

        return $this->getClient()
            ->request($resourceType.'/metafields', 'get', $parameters)
            ->collect('metafields');
    }
}
