<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class Insight extends AbstractService
{
    public function getSiteStats(): array
    {
        return $this->getClient()
            ->request('stats', 'get')
            ->json();
    }

    public function getMrr(array $parameters = []): array
    {
        $this->validatePayload($parameters, [
            'at_time' => 'sometimes|date',
            'subscription_id' => 'sometimes|integer',
        ]);

        return $this->getClient()
            ->request('mrr', 'get', $parameters)
            ->json('mrr', []);
    }

    public function listMrrMovements(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'direction' => 'sometimes|string|in:asc,desc',
            'subscription_id' => 'sometimes|integer',
        ]);

        return $this->getClient()
            ->request('mrr_movements', 'get', $parameters)
            ->collect('mrr.movements');
    }

    public function listMrrPerSubscription(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'direction' => 'sometimes|string|in:asc,desc',
            'at_time' => 'sometimes|date',
            'filter' => 'sometimes|array',
            'filter.subscription_ids' => 'sometimes|string',
        ]);

        return $this->getClient()
            ->request('subscriptions_mrr', 'get', $parameters)
            ->collect('subscriptions_mrr');
    }
}
