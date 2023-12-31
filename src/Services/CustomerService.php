<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;

class CustomerService extends AbstractService
{
    public function create(Model $model): ChargifyObject
    {
        $parameters = [
            'reference' => $model->getKey(),
            'first_name' => $model->firstname,
            'last_name' => $model->lastname,
            'email' => $model->email,
            'phone' => $model->phone_number,
            'address' => $model->address,
            'country' => $model->country,
            'state' => $model->state,
            'city' => $model->city,
            'zip' => $model->zip,
            'locale' => 'en-US',
        ];

        return $this->post('customers', ['customer' => $parameters]);
    }

    public function update(string $customerId, array $parameters): ChargifyObject
    {
        return $this->put('customers/'.$customerId, ['customer' => $parameters]);
    }

    public function deleteCustomer(string $customerId): void
    {
        $this->delete('customers/'.$customerId);
    }

    public function list(array $parameters = []): Collection
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

        return $this->get('customers', $parameters);
    }

    public function listCustomerSubscriptions(string $customerId): Collection
    {
        return $this->get('customers/'.$customerId.'/subscriptions');
    }

    public function find(string $customerId): ChargifyObject
    {
        return $this->get('customers/'.$customerId);
    }

    public function getByReference(string $reference): ChargifyObject
    {
        return $this->get('customers/lookup', ['reference' => $reference]);
    }
}
