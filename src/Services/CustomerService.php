<?php

namespace WinLocalInc\Chjs\Services;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\Customer;

class CustomerService extends AbstractService
{
    public function create(Model $model): Customer
    {
        $parameters = [
            'reference' => $model->getKey(),
            'first_name' => $model->firstname,
            'last_name' => $model->lastname,
            'email' => $model->email,
            'phone' => $model->phone_number,
            'vat_number' => $model->vat_number,
            'address' => $model->address,
            'address_2' => $model->address_2,
            'country' => $model->country,
            'state' => $model->state,
            'city' => $model->city,
            'zip' => $model->zip,
            'locale' => 'en-US',
        ];

        return $this->post('customers',  ['customer' => $parameters]);
    }

    public function update(string $customerId, array $parameters): Customer
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

    public function getById(string $customerId): Customer
    {
        return $this->get('customers/'.$customerId);
    }

    public function getByReference(string $reference): Customer
    {
        return $this->get('customers/lookup', ['reference' => $reference]);
    }
}
