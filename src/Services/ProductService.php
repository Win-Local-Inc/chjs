<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;

class ProductService extends AbstractService
{
    public function create(string $productFamilyId, array $parameters): ChargifyObject
    {
        return $this->post('product_families/'.$productFamilyId.'/products', ['product' => $parameters]);
    }

    public function update(string $productId, array $parameters): ChargifyObject
    {
        return $this->put('products/'.$productId, ['product' => $parameters]);
    }

    public function archive(string $productId): ChargifyObject
    {
        return $this->delete('products/'.$productId);
    }

    public function list(array $options = []): Collection
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

        return $this->get('products', $parameters);

    }

    public function find(string $productId): ChargifyObject
    {
        return $this->get('products/'.$productId);
    }

    public function getByHandle(string $handle): ChargifyObject
    {
        return $this->get('products/handle/'.$handle);
    }
}
