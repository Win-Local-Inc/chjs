<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class ProductFamily extends AbstractService
{
    public function create(array $parameters): array
    {
        $this->validatePayload($parameters, [
            'name' => 'required|string',
            'description' => 'required|string',
        ]);

        return $this->getClient()
            ->request('product_families', 'post', ['product_family' => $parameters])
            ->json('product_family', []);
    }

    public function listProductFamiles(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'date_field' => 'sometimes|string|in:updated_at,created_at',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        return $this->getClient()
            ->request('product_families', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['product_family']);
    }

    public function getProductFamilyById(string $productFamilyId): array
    {
        return $this->getClient()
            ->request('product_families/'.$productFamilyId, 'get')
            ->json('product_family', []);
    }

    public function listProductsForProductFamily(string $productFamilyId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'include_archived' => 'sometimes|boolean',
            'date_field' => 'sometimes|string|in:updated_at,created_at',
            'direction' => 'sometimes|string|in:asc,desc',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/products', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['product']);
    }
}
