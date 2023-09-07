<?php

namespace Obsolete\Traits;

use App\Models\Chargify\ChargifyCustomer;
use App\Models\Chargify\ChargifySubscription;
use Obsolete\ChargifySystem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait Chargifyable
{
    protected ?ChargifySystem $chargifySystem = null;

    protected function chargifySystem(): ChargifySystem
    {
        return $this->chargifySystem ?: $this->chargifySystem = resolve(ChargifySystem::class);
    }

    public function chargifyCustomer(): HasOne
    {
        return $this->hasOne(ChargifyCustomer::class, 'user_id', 'user_id');
    }

    public function chargifySubscriptions(): HasMany
    {
        return $this->hasMany(ChargifySubscription::class, 'user_id', 'user_id');
    }

    public function createCustomer(array $options = [], string $parentCustomerId = null): static
    {
        if (! $this->chargifyCustomer?->id) {
            $this->chargifySystem()->upsertCustomer($this, $options, $parentCustomerId);
        }

        return $this;
    }

    public function updateCustomer(array $options = [], string $parentCustomerId = null): static
    {
        $this->chargifySystem()->upsertCustomer($this, $options, $parentCustomerId);

        return $this;
    }

    public function createPaymnetProfile(string $token): static
    {
        if (! $this->chargifyCustomer?->id) {
            $this->chargifySystem()->upsertCustomerWithPaymentProfileFromToken($this, $token);
        } else {
            $this->chargifySystem()->createPaymnetProfile($this->chargifyCustomer?->id, $token);
        }

        return $this;
    }
}
