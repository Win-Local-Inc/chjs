<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class Product extends AbstractService
{
    public function create(string $productFamilyId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'name' => 'required|string',
            'description' => 'required|string',
            'price_in_cents' => 'required|integer',
            'interval' => 'required|integer',
            'interval_unit' => 'required|string|in:month,day',
        ]);

        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/products', 'post', ['product' => $parameters])
            ->json('product', []);
    }

    public function update(string $productId, array $parameters): array
    {
        return $this->getClient()
            ->request('products/'.$productId, 'put', ['product' => $parameters])
            ->json('product', []);
    }

    public function archive(string $productId): array
    {
        return $this->getClient()
            ->request('products/'.$productId, 'delete')
            ->json('product', []);
    }

    public function listProducts(array $parameters = []): Collection
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
            ->request('products', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['product']);

    }

    public function getProductById(string $productId): array
    {
        return $this->getClient()
            ->request('products/'.$productId, 'get')
            ->json('product', []);
    }

    public function getProductByHandle(string $handle): array
    {
        return $this->getClient()
            ->request('products/handle/'.$handle, 'get')
            ->json('product', []);
    }
}
