<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class Offer extends AbstractService
{
    public function create(array $parameters): array
    {
        $this->validatePayload($parameters, [
            'name' => 'required|string',
            'handle' => 'required|string',
            'product_id' => 'required_without:product_price_point_id|integer',
            'product_price_point_id' => 'required_without:product_id|integer',
            'components' => 'sometimes|array',
            'components.*.component_id' => 'required_with:components|integer',
            'components.*.starting_quantity' => 'required_with:components|integer',
            'coupons' => 'sometimes|array',
        ]);

        return $this->getClient()
            ->request('offers', 'post', ['offer' => $parameters])
            ->json('offer', []);
    }

    public function archive(string $offerId): void
    {
        $this->getClient()
            ->request('offers/'.$offerId.'/archive', 'put');
    }

    public function unarchive(string $offerId): void
    {
        $this->getClient()
            ->request('offers/'.$offerId.'/unarchive', 'put');
    }

    public function getOfferById(string $offerId): array
    {
        return $this->getClient()
            ->request('offers/'.$offerId, 'get')
            ->json('offer', []);
    }

    public function listOffers(): Collection
    {
        return $this->getClient()
            ->request('offers', 'get')
            ->collect('offers');

    }
}
