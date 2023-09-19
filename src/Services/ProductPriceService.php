<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;

class ProductPriceService extends AbstractService
{
    public function create(string $productId, array $parameters): ChargifyObject
    {
        $this->validatePayload($parameters, [
            'name' => 'required|string',
            'price_in_cents' => 'required|integer',
            'interval' => 'required|integer',
            'interval_unit' => 'required|string|in:month,day',
        ]);

        return $this->post('products/'.$productId.'/price_points', ['price_point' => $parameters]);
    }

    public function update(string $productId, string $pricePointId, array $parameters): ChargifyObject
    {
        return $this->put('products/'.$productId.'/price_points/'.$pricePointId, ['price_point' => $parameters]);
    }

    public function archive(string $productId, string $pricePointId): ChargifyObject
    {
        return $this->delete('products/'.$productId.'/price_points/'.$pricePointId);
    }

    public function unarchive(string $productId, string $pricePointId): ChargifyObject
    {
        return $this->request('products/'.$productId.'/price_points/'.$pricePointId.'/unarchive', 'patch');
    }

    public function setProductDefaultPricePoint(string $productId, string $pricePointId): ChargifyObject
    {
        return $this->patch('products/'.$productId.'/price_points/'.$pricePointId.'/default');
    }

    public function listProductPricePoints(string $productId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'filter' => 'sometimes|array',
            'filter.type' => 'sometimes|string',
        ]);

        return $this->get('products/'.$productId.'/price_points', $parameters);
    }

    public function list(array $options = []): Collection
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

        return $this->get('products_price_points', $parameters);
    }

    public function find(string $productId, string $pricePointId): ChargifyObject
    {
        return $this->get('products/'.$productId.'/price_points/'.$pricePointId);
    }
}
