<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class Component extends AbstractService
{
    public function createComponent(string $productFamilyId, string $kind, array $parameters): array
    {
        $this->validatePayload(['kind' => $kind], [
            'kind' => 'required|in:metered_component,quantity_based_component,on_off_component,prepaid_usage_component,event_based_component',
        ]);

        $this->validatePayload($parameters, [
            'name' => 'required|string',
            'unit_name' => 'required|string',
            'pricing_scheme' => 'required|in:per_unit,volume,tiered,stairstep',
        ]);

        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/'.$kind.'s', 'post', [$kind => $parameters])
            ->json('component', []);
    }

    public function archiveComponent(string $productFamilyId, string $componentId): array
    {
        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/components/'.$componentId, 'delete')
            ->json('component', []);
    }

    public function getComponentById(string $productFamilyId, string $componentId): array
    {
        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/components/'.$componentId, 'get')
            ->json('component', []);
    }

    public function getComponentByHandle(string $handle): array
    {
        return $this->getClient()
            ->request('components/lookup', 'get', ['handle' => $handle])
            ->json('component', []);
    }

    public function listComponents(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'include_archived' => 'sometimes|boolean',
            'date_field' => 'sometimes|string|in:updated_at,created_at',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        return $this->getClient()
            ->request('components', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['component']);
    }

    public function listComponentsForProductFamily(string $productFamilyId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'include_archived' => 'sometimes|boolean',
            'date_field' => 'sometimes|string|in:updated_at,created_at',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/components', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['component']);
    }

    public function listComponentPricePoints(string $componentId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->getClient()
            ->request('components/'.$componentId.'/price_points', 'get', $parameters)
            ->collect('price_points');
    }

    public function listPricePoints(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->getClient()
            ->request('components_price_points', 'get', $parameters)
            ->collect('price_points');
    }

    public function createPricePoint(string $componentId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'name' => 'required|string',
            'prices' => 'required|array',
            'pricing_scheme' => 'required|in:per_unit,volume,tiered,stairstep',
        ]);

        return $this->getClient()
            ->request('components/'.$componentId.'/price_points', 'post', ['price_point' => $parameters])
            ->json('price_point', []);
    }

    public function createCurrencyPricesForPricePoint(string $pricePointId, array $parameters): array
    {
        return $this->getClient()
            ->request('price_points/'.$pricePointId.'/currency_prices', 'post', ['currency_prices' => $parameters])
            ->json();
    }

    public function updateCurrencyPricesForPricePoint(string $pricePointId, array $parameters): array
    {
        return $this->getClient()
            ->request('price_points/'.$pricePointId.'/currency_prices', 'put', ['currency_prices' => $parameters])
            ->json();
    }

    public function updatePricePoint(string $componentId, string $pricePointId, array $parameters): array
    {
        return $this->getClient()
            ->request('components/'.$componentId.'/price_points/'.$pricePointId, 'post', ['price_point' => $parameters])
            ->json('price_point', []);
    }

    public function archivePricePoint(string $componentId, string $pricePointId): array
    {
        return $this->getClient()
            ->request('components/'.$componentId.'/price_points/'.$pricePointId, 'delete')
            ->json('price_point', []);
    }

    public function unarchivePricePoint(string $componentId, string $pricePointId): array
    {
        return $this->getClient()
            ->request('components/'.$componentId.'/price_points/'.$pricePointId.'/unarchive', 'put')
            ->json('price_point', []);
    }

    public function setDefaultPricePoint(string $componentId, array $pricePointId): void
    {
        $this->getClient()
            ->request('components/'.$componentId.'/price_points/'.$pricePointId.'/default.json', 'put');
    }

    public function createSegmentForComponent(string $componentId, string $pricePointId, array $parameters): array
    {
        return $this->getClient()
            ->request('components/'.$componentId.'/price_points/'.$pricePointId.'/segments', 'post', ['segment' => $parameters])
            ->json('segment', []);
    }

    public function updateSegmentForComponent(string $componentId, string $pricePointId, string $segmentId, array $parameters): array
    {
        return $this->getClient()
            ->request('components/'.$componentId.'/price_points/'.$pricePointId.'/segments/'.$segmentId, 'put', ['segment' => $parameters])
            ->json('segment', []);
    }

    public function deleteSegmentForComponent(string $componentId, string $pricePointId, string $segmentId): void
    {
        $this->getClient()
            ->request('components/'.$componentId.'/price_points/'.$pricePointId.'/segments/'.$segmentId, 'delete');
    }

    public function listSegmentsForComponent(string $componentId, string $pricePointId, array $parameters = []): Collection
    {
        return $this->getClient()
            ->request('components/'.$componentId.'/price_points/'.$pricePointId.'/segments', 'get', $parameters)
            ->collect('segments');
    }
}
