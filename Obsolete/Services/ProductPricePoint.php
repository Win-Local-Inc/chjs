<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class ProductPricePoint extends AbstractService
{
    public function create(string $productId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'name' => 'required|string',
            'price_in_cents' => 'required|integer',
            'interval' => 'required|integer',
            'interval_unit' => 'required|string|in:month,day',
        ]);

        return $this->getClient()
            ->request('products/'.$productId.'/price_points', 'post', ['price_point' => $parameters])
            ->json('price_point', []);
    }

    public function createBulk(string $productId, array $parameters): Collection
    {
        $this->validatePayload(['parameters' => $parameters], [
            'parameters.*.name' => 'required|string',
            'parameters.*.price_in_cents' => 'required|integer',
            'parameters.*.interval' => 'required|integer',
            'parameters.*.interval_unit' => 'required|string|in:month,day',
        ]);

        return $this->getClient()
            ->request('products/'.$productId.'/price_points/bulk', 'post', ['price_points' => $parameters])
            ->collect('price_points');
    }

    public function update(string $productId, string $pricePointId, array $parameters): array
    {
        return $this->getClient()
            ->request('products/'.$productId.'/price_points/'.$pricePointId, 'put', ['price_point' => $parameters])
            ->json('price_point', []);
    }

    public function archive(string $productId, string $pricePointId): array
    {
        return $this->getClient()
            ->request('products/'.$productId.'/price_points/'.$pricePointId, 'delete')
            ->json('price_point', []);
    }

    public function unarchive(string $productId, string $pricePointId): array
    {
        return $this->getClient()
            ->request('products/'.$productId.'/price_points/'.$pricePointId.'/unarchive', 'patch')
            ->json('price_point', []);
    }

    public function setProductDefaultPricePoint(string $productId, string $pricePointId): array
    {
        return $this->getClient()
            ->request('products/'.$productId.'/price_points/'.$pricePointId.'/default', 'patch')
            ->json('product', []);
    }

    public function listProductPricePoints(string $productId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'filter' => 'sometimes|array',
            'filter.type' => 'sometimes|string',
        ]);

        return $this->getClient()
            ->request('products/'.$productId.'/price_points', 'get', $parameters)
            ->collect('price_points');
    }

    public function listPricePoints(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'direction' => 'sometimes|string|in:asc,desc',
            'include' => 'sometimes|string|in:currency_prices',
            'filter' => 'sometimes|array',
        ]);

        return $this->getClient()
            ->request('products_price_points', 'get', $parameters)
            ->collect('price_points');
    }

    public function getPricePointById(string $productId, string $pricePointId): array
    {
        return $this->getClient()
            ->request('products/'.$productId.'/price_points/'.$pricePointId, 'get')
            ->json('price_point', []);
    }

    public function createCurrencyPrices(string $pricePointId, array $parameters): Collection
    {
        $this->validatePayload(['parameters' => $parameters], [
            'parameters.*.currency' => 'required|string',
            'parameters.*.price' => 'required|integer',
            'parameters.*.role' => 'required|string|in:baseline,trial,initial',
        ]);

        return $this->getClient()
            ->request('product_price_points/'.$pricePointId.'/currency_prices', 'post', ['currency_prices' => $parameters])
            ->collect();
    }
}
