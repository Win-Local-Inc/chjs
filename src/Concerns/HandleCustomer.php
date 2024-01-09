<?php

namespace WinLocalInc\Chjs\Concerns;

use Chargify\Customer;
use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Exceptions\CustomerAlreadyCreated;
use WinLocalInc\Chjs\Exceptions\InvalidCustomer;
use WinLocalInc\Chjs\Models\Subscription;

trait HandleCustomer
{
    public function chargifyId(): ?int
    {
        return $this->chargify_id;
    }

    public function hasChargifyId(): bool
    {
        return ! is_null($this->chargify_id);
    }

    /**
     * @throws InvalidCustomer
     */
    protected function assertCustomerExists(): void
    {
        if (! $this->hasChargifyId()) {
            throw InvalidCustomer::notYetCreated($this);
        }
    }

    /**
     * @throws CustomerAlreadyCreated
     */
    public function createAsChargifyCustomer(string $token = null, array $billingAddress = []): object
    {
        if ($this->hasChargifyId()) {
            throw CustomerAlreadyCreated::exists($this);
        }
        $paymentProfile = null;
        $customer = maxio()->customer->create($this);

        if ($token) {
            $paymentProfile = maxio()->paymentProfile->create(customerId: $customer->id, chargifyToken: $token);

            try {
                maxio()->paymentProfile->update(paymentProfileId: $paymentProfile->id,parameters: $billingAddress);
            }catch (\Exception $e) {
                logger($e->getMessage());
            }
        }

        $this->chargify_id = $customer->id;

        $this->saveQuietly();



        return (object) ['chargifyId' => $customer->id, 'paymentProfile' => $paymentProfile];
    }

    public function updateChargifyCustomer(array $options = []): ChargifyObject
    {
        return maxio()->customer->update(
            $this->chargify_id,
            $options
        );
    }

    /**
     * @throws CustomerAlreadyCreated
     */
    public function createOrGetChargifyCustomer(): object
    {
        if ($this->hasChargifyId()) {
            return maxio()->customer->find($this->hasChargifyId());
        }

        return $this->createAsChargifyCustomer();
    }

    /**
     * Get the Chargify customer for the model.
     *
     * @return Customer
     */
    public function asChargifyCustomer(): ChargifyObject
    {
        $this->assertCustomerExists();

        return maxio()->customer->find(
            $this->chargify_id
        );
    }

    public function syncChargifyCustomerDetails(): ChargifyObject
    {
        return $this->updateChargifyCustomer([
            'reference' => $this->getKey(),
            'first_name' => $this->firstname,
            'last_name' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone_number,
            'address' => $this->address,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'zip' => $this->zip,
            'locale' => 'en-US',
        ]);
    }

    public function subscriptions($status = null)
    {
        return Subscription::where('user_id', $this->user_id)
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })->get();
    }

    public function getPaymentMethods(): Collection
    {
        return maxio()->paymentProfile->list(['customer_id' => $this->chargifyId()]);
    }

    public function addPaymentMethods(string $token): ChargifyObject
    {
        return maxio()->paymentProfile->create(customerId: $this->chargifyId(), chargifyToken: $token);
    }
}
