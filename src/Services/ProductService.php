<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;

class ProductService extends AbstractService
{
    public function create(string $productFamilyId, array $parameters): object
    {
        return $this->post('product_families/'.$productFamilyId.'/products', ['product' => $parameters])
            ->object()->product;
    }

    public function update(string $productId, array $parameters): object
    {
        return $this->put('products/'.$productId, ['product' => $parameters])->object()->product;
    }

    public function archive(string $productId): object
    {
        return $this->delete('products/'.$productId)->object()->product;
    }

    public function list(array $options = []): object
    {
        $this->validatePayload($options, [
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

        $parameters = array_merge([
            'page' => 1,
            'per_page' => 100,
            'include_archived' => true,
        ], $options);

        return (object) $this->get('products', $parameters)
            ->collect()
            ->map(fn ($item) => (object) $item['product'])
            ->all();

    }

    public function getProductById(string $productId): array
    {
        return $this->get('products/'.$productId)->object()->product;
    }

    public function getProductByHandle(string $handle): array
    {
        return $this->get('products/handle/'.$handle)->object()->product;
    }
}
