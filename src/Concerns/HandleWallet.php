<?php

namespace WinLocalInc\Chjs\Concerns;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Exceptions\CustomerAlreadyCreated;
use WinLocalInc\Chjs\Exceptions\InvalidCustomer;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;

trait HandleWallet
{


    /**
     * @throws InvalidCustomer
     */
    public function applyBalance($qty)
    {
        $this->assertCustomerExists();

        $subscription = $this->firstActiveSubscription();

        if (!$subscription)
        {
            throw new \Exception("no subscription");
        }

        $adComponentPrice = ComponentPrice::find(config('chjs.ads_component'));

        $data = ['component_id' => $adComponentPrice->component_id, 'quantity' => $qty];

        maxio()->subscriptionComponent->updateQuantity(
            subscriptionId: $subscription->subscription_id,
            componentId: $adComponentPrice->component_id,
            options: $data
        );

        $maxioComponent = maxio()->subscriptionComponent
            ->list(subscriptionId: $subscription->subscription_id)
            ->where('component_id', $adComponentPrice->component_id)->first();

        $componentPrice = maxio()->componentPrice->list(['filter' => ['ids' => $maxioComponent->price_point_id]]);

        SubscriptionComponent::updateOrCreate(
            [   'subscription_id' => $subscription->subscription_id,
                'component_id' => $maxioComponent->component_id,
            ],
            [
                'component_handle' => $maxioComponent->component_handle,
                'component_price_handle' => $maxioComponent->price_point_handle,
                'component_price_id' => $maxioComponent->price_point_id,
                'subscription_component_price' => $componentPrice->first()->prices->first()->unit_price,
                'subscription_component_quantity' => $maxioComponent->allocated_quantity,
                'created_at' => $maxioComponent->created_at,
                'updated_at' => $maxioComponent->updated_at,
            ]
        );

        return $this;
    }


    /**
     * Get the total balance of the customer.
     *
     * @return string
     */
    public function balance()
    {
        return $this->formatAmount($this->rawBalance());
    }

    /**
     * Get the raw total balance of the customer.
     *
     * @return int
     */
    public function rawBalance()
    {
        if (! $this->hasChargifyId()) {
            return 0;
        }

        return $this->asChargifyCustomer()->balance;
    }

    /**
     * Return a customer's balance transactions.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function balanceTransactions($limit = 10, array $options = [])
    {
        if (! $this->hasChargifyId()) {
            return new Collection();
        }

        $transactions = $this->chargify()
            ->customers
            ->allBalanceTransactions($this->chargify_id, array_merge(['limit' => $limit], $options));

        return Collection::make($transactions->data)->map(function ($transaction) {
            return new CustomerBalanceTransaction($this, $transaction);
        });
    }


    public function creditBalance($amount, $description = null, array $options = [])
    {
        return $this->applyBalance(-$amount, $description, $options);
    }

    public function debitBalance($amount, $description = null, array $options = [])
    {
        return $this->applyBalance($amount, $description, $options);
    }



}
