<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Chargify\Component;

class ComponentService extends AbstractService
{
    public function create(string $productFamilyId, string $kind, array $parameters): ChargifyObject
    {
        return $this->post('product_families/'.$productFamilyId.'/'.$kind.'s', [$kind => $parameters]);
    }

    public function archive(string $productFamilyId, int $componentId): ChargifyObject
    {
        return $this->delete('product_families/'.$productFamilyId.'/components/'.$componentId);
    }

    public function getById(string $productFamilyId, int $componentId): ChargifyObject
    {
        return $this->get('product_families/'.$productFamilyId.'/components/'.$componentId);
    }

    public function getByHandle(string $handle): ChargifyObject
    {
        return $this->get('components/lookup', ['handle' => $handle]);
    }

    public function list(array $options = []): Collection
    {
        $this->validatePayload($options, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'include_archived' => 'sometimes|boolean',
            'date_field' => 'sometimes|string|in:updated_at,created_at',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        $parameters = array_merge(
            [
                'page' => 1,
                'per_page' => 100,
                'include_archived' => true,
            ],
            $options
        );

        return $this->get('components',  $parameters);
    }

    public function listComponentsForProductFamily(string $productFamilyId, array $options = []): Collection
    {
        $this->validatePayload($options, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'include_archived' => 'sometimes|boolean',
            'date_field' => 'sometimes|string|in:updated_at,created_at',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        return $this->get('product_families/'.$productFamilyId.'/components', $options);
    }

    public function pricePoints(int $componentId, array $parameters = []): ChargifyObject
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->get('components/'.$componentId.'/price_points', $parameters);
    }

    public function findPricePoint(int $componentId, int $pricePoint): ChargifyObject
    {
//        return (object) $this
//            ->get('components/'.$componentId.'/price_points')
//            ->collect('price_points')
//            ->map(fn ($item) => (object) $item)
//            ->filter(fn ($item) => $item->id == $pricePoint)
//            ->map(function ($item) {
//                $item->prices =(object) $item->prices[0] ;
//                $item->prices->unit_price = (int) number_format($item->prices->unit_price * 100, 0, '.', '');
//                return $item;
//            })
//            ->first();

//        return (object) $this
//            ->get('components/'.$componentId.'/price_points')
//            ->collect('price_points')
//            ->map(fn ($item) => (object) $item)
//            ->filter(fn ($item) => $item->id == $pricePoint)
//            ->map(function ($item) {
//                $item->prices =(object) $item->prices[0] ;
//                $item->prices->unit_price = (int) number_format($item->prices->unit_price * 100, 0, '.', '');
//                return $item;
//            })
//            ->first();
    }

    public function setDefaultPricePoint(string $componentId, string $pricePointId): void
    {
        $this->put('components/'.$componentId.'/price_points/'.$pricePointId.'/default.json');
    }
}
