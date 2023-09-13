<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;

class ProductPriceService extends AbstractService
{
    public function create(string $productId, array $parameters): object
    {
        $this->validatePayload($parameters, [
            'name' => 'required|string',
            'price_in_cents' => 'required|integer',
            'interval' => 'required|integer',
            'interval_unit' => 'required|string|in:month,day',
        ]);

        return $this
            ->post('products/'.$productId.'/price_points', ['price_point' => $parameters])
            ->object()->price_point;
    }

    public function createBulk(string $productId, array $parameters): Collection
    {
        $this->validatePayload(['parameters' => $parameters], [
            'parameters.*.name' => 'required|string',
            'parameters.*.price_in_cents' => 'required|integer',
            'parameters.*.interval' => 'required|integer',
            'parameters.*.interval_unit' => 'required|string|in:month,day',
        ]);

        return $this
            ->post('products/'.$productId.'/price_points/bulk', ['price_points' => $parameters])
            ->collect('price_points');
    }

    public function update(string $productId, string $pricePointId, array $parameters): object
    {
        return $this
            ->put('products/'.$productId.'/price_points/'.$pricePointId, ['price_point' => $parameters])
            ->object()->price_point;
    }

    public function archive(string $productId, string $pricePointId): object
    {
        return $this
            ->delete('products/'.$productId.'/price_points/'.$pricePointId)
            ->object()->price_point;
    }

    public function unarchive(string $productId, string $pricePointId): object
    {
        return $this
            ->request('products/'.$productId.'/price_points/'.$pricePointId.'/unarchive', 'patch')
            ->object()->price_point;
    }

    public function setProductDefaultPricePoint(string $productId, string $pricePointId): object
    {
        return $this
            ->patch('products/'.$productId.'/price_points/'.$pricePointId.'/default')
            ->object()->product;
    }

    public function listProductPricePoints(string $productId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'filter' => 'sometimes|array',
            'filter.type' => 'sometimes|string',
        ]);

        return $this
            ->get('products/'.$productId.'/price_points', $parameters)
            ->collect('price_points');
    }

    public function list(array $options = []): object
    {
        $this->validatePayload($options, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'direction' => 'sometimes|string|in:asc,desc',
            'include' => 'sometimes|string|in:currency_prices',
            'filter' => 'sometimes|array',
        ]);

        $parameters = array_merge([
            'page' => 1,
            'per_page' => 100,
            'filter' => [
                'type' => 'catalog,default',
            ],
        ], $options);

        return (object) $this->get('products_price_points', $parameters)
            ->collect('price_points')
            ->map(fn ($item) => (object) $item)
            ->all();
    }

    public function getPricePointById(string $productId, string $pricePointId): object
    {
        return $this
            ->get('products/'.$productId.'/price_points/'.$pricePointId)
            ->object()->price_point;
    }

    public function createCurrencyPrices(string $pricePointId, array $parameters): Collection
    {
        $this->validatePayload(['parameters' => $parameters], [
            'parameters.*.currency' => 'required|string',
            'parameters.*.price' => 'required|integer',
            'parameters.*.role' => 'required|string|in:baseline,trial,initial',
        ]);

        return $this
            ->post('product_price_points/'.$pricePointId.'/currency_prices', ['currency_prices' => $parameters])
            ->collect();
    }
}
