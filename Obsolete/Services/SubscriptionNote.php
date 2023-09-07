<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class SubscriptionNote extends AbstractService
{
    public function create(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'body' => 'required|string',
            'sticky' => 'required|boolean',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/notes', 'post', ['note' => $parameters])
            ->json('note', []);
    }

    public function update(string $subscriptionId, string $noteId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'body' => 'required|string',
            'sticky' => 'required|boolean',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/notes/'.$noteId, 'put', ['note' => $parameters])
            ->json('note', []);
    }

    public function getById(string $subscriptionId, string $noteId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/notes/'.$noteId, 'get')
            ->json('note', []);
    }

    public function delete(string $subscriptionId, string $noteId): void
    {
        $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/notes/'.$noteId, 'delete');
    }

    public function list(string $subscriptionId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/notes', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['note']);
    }
}
