<?php

namespace WinLocalInc\Chjs\Concerns;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Exceptions\CustomerAlreadyCreated;
use WinLocalInc\Chjs\Exceptions\InvalidCustomer;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;

trait HandleCustomer
{
    //adding support to ads component
    // maybe new trait and add price param in env file
    //webhook usage_quantity - current
    //
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
    public function createAsChargifyCustomer(string $token = null): object
    {
        if ($this->hasChargifyId()) {
            throw CustomerAlreadyCreated::exists($this);
        }
        $paymentProfile = null;
        $customer = maxio()->customer->create($this);

        if ($token) {
            $paymentProfile = maxio()->paymentProfile->create(customerId: $customer->id, chargifyToken: $token);
        }

        $this->chargify_id = $customer->id;

        $this->saveQuietly();

        return (object) ['chargifyId' => $customer->id, 'paymentProfile' => $paymentProfile];
    }

    public function updateChargifyCustomer(array $options = []): ChargifyObject
    {
        return maxio()->customer->update(
            $this->chargify_id, $options
        );
    }

    /**
     * @throws CustomerAlreadyCreated
     */
    public function createOrGetChargifyCustomer():object
    {
        if ($this->hasChargifyId()) {
            return maxio()->customer->find($this->hasChargifyId());
        }

        return $this->createAsChargifyCustomer();
    }

    /**
     * Get the Chargify customer for the model.
     *
     * @return \Chargify\Customer
     */
    public function asChargifyCustomer(array $expand = [])
    {
        $this->assertCustomerExists();

        return $this->chargify()->customers->retrieve(
            $this->chargify_id, ['expand' => $expand]
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
            'vat_number' => $this->vat_number,
            'address' => $this->address,
            'address_2' => $this->address_2,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'zip' => $this->zip,
            'locale' => 'en-US',
        ]);
    }


    public function subscriptions($status = null)
    {
        return Subscription::where( 'user_id', $this->user_id )
            ->when( $status, function ($q) use ($status) {
                $q->where('status', $status);
            })->get();
    }

}
