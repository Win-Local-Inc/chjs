<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class Customer extends AbstractService
{
    public function create(array $parameters): array
    {
        $this->validatePayload($parameters, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'country' => ['sometimes', 'regex:/^[A-Z]{2}$/'],
        ]);

        return $this->getClient()
            ->request('customers', 'post', ['customer' => $parameters])
            ->json('customer', []);
    }

    public function update(string $customerId, array $parameters): array
    {
        return $this->getClient()
            ->request('customers/'.$customerId, 'put', ['customer' => $parameters])
            ->json('customer', []);
    }

    public function delete(string $customerId): void
    {
        $this->getClient()
            ->request('customers/'.$customerId, 'delete');
    }

    public function listCustomers(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'date_field' => 'sometimes|string|in:updated_at,created_at',
            'direction' => 'sometimes|string|in:asc,desc',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'q' => 'sometimes|string',
        ]);

        return $this->getClient()
            ->request('customers', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['customer']);
    }

    public function listCustomerSubscriptions(string $customerId): Collection
    {
        return $this->getClient()
            ->request('customers/'.$customerId.'/subscriptions', 'get')
            ->collect();
    }

    public function getCustomerById(string $customerId): array
    {
        return $this->getClient()
            ->request('customers/'.$customerId, 'get')
            ->json('customer', []);
    }

    public function getCustomerByReference(string $reference): array
    {
        return $this->getClient()
            ->request('customers/lookup', 'get', ['reference' => $reference])
            ->json('customer', []);
    }
}
