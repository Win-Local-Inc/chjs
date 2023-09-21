<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;

class ComponentPriceService extends AbstractService
{
    public function create(string $componentId, array $parameters): ChargifyObject
    {
        $this->validatePayload($parameters, [
            'name' => 'required|string',
            'prices' => 'required|array',
            'pricing_scheme' => 'required|in:per_unit,volume,tiered,stairstep',
        ]);

        return $this->post('components/'.$componentId.'/price_points', ['price_point' => $parameters]);
    }

    public function update(string $componentId, string $pricePointId, array $parameters): ChargifyObject
    {
        return $this
            ->post('components/'.$componentId.'/price_points/'.$pricePointId, ['price_point' => $parameters]);
    }

    public function list(array $options = []): Collection
    {
        $this->validatePayload($options, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        $parameters = array_merge_recursive([
            'page' => 1,
            'per_page' => 100,
            'filter' => [
                'type' => 'catalog,default,custom',
            ],
        ],
            $options
        );

        return $this->get('components_price_points', $parameters);
    }

    public function find(string $pricePoint): ChargifyObject
    {
        $parameters = [
            'page' => 1,
            'per_page' => 100,
            'filter' => [
                'type' => 'catalog,default,custom',
                'ids' => $pricePoint,
            ],
        ];

        return $this->get('components_price_points', $parameters)[0];
    }

    public function createCurrencyPricesForPricePoint(string $pricePointId, array $parameters): ChargifyObject
    {
        return $this
            ->post('price_points/'.$pricePointId.'/currency_prices', ['currency_prices' => $parameters]);
    }

    public function updateCurrencyPricesForPricePoint(string $pricePointId, array $parameters): ChargifyObject
    {
        return $this
            ->put('price_points/'.$pricePointId.'/currency_prices', ['currency_prices' => $parameters]);
    }

    public function archive(string $componentId, string $pricePointId): ChargifyObject
    {
        return $this->delete('components/'.$componentId.'/price_points/'.$pricePointId);
    }

    public function unarchive(string $componentId, string $pricePointId): object
    {
        return $this->put('components/'.$componentId.'/price_points/'.$pricePointId.'/unarchive');
    }

    public function setDefault(string $componentId, string $pricePointId): void
    {
        $this->put('components/'.$componentId.'/price_points/'.$pricePointId.'/default.json');
    }
}
