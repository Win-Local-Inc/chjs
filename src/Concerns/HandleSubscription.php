<?php

namespace WinLocalInc\Chjs\Concerns;

use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Chargify\Subscription;
use WinLocalInc\Chjs\Enums\ProductPricing;
use WinLocalInc\Chjs\Enums\SubscriptionInterval;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Models\SubscriptionComponent;
use WinLocalInc\Chjs\ProductStructure;
use WinLocalInc\Chjs\SubscriptionBuilder;

trait HandleSubscription
{
    /**
     * @throws \Exception
     */
    public function newSubscription(ProductPricing $productPricing, int $productCustomPrice = null): SubscriptionBuilder
    {
        return new SubscriptionBuilder(
            productPricing: $productPricing,
            productCustomPrice: $productCustomPrice,
            workspace: $this
        );
    }

    //adding status check
    //add exceptions

    public function swapSubscriptionProduct(ProductPrice $productPrice, int $customPrice = null, array $options = []): static
    {
        //Subscriptions should be in the active or trialing

        $data = ['product_id' => $productPrice->product_id];

        if (! $customPrice) {
            $data['product_price_point_id'] = $productPrice->product_price_id;
        } else {
            $data['custom_price']['price_in_cents'] = $customPrice;
            $data['custom_price']['interval'] = $productPrice->product_price_interval->getInterval();
            $data['custom_price']['interval_unit'] = 'month';
        }

        $data = array_merge($options, $data);

        return $this->migrateProduct($data);
    }

    public function swapSubscriptionProductPriceUsingTrial(ProductPrice $productPrice, int $trialIntervalInDays): static
    {
        $data = [
            'product_id' => $productPrice->product->product_id,
            'include_trial' => true,
            'preserve_period' => false,
        ];

        $data['custom_price']['price_in_cents'] = $productPrice->product_price_in_cents;
        $data['custom_price']['interval'] = $productPrice->product_price_interval->getInterval();
        $data['custom_price']['interval_unit'] = 'month';
        $data['custom_price']['trial_price_in_cents'] = 0;
        $data['custom_price']['trial_interval_unit'] = 'day';
        $data['custom_price']['trial_interval'] = $trialIntervalInDays;
        $data['custom_price']['initial_charge_after_trial'] = true;

        return $this->migrateProduct($data);
    }

    public function addSubscriptionComponent(Component $component, int $qty, int $customPrice = null, array $options = []): static
    {
        $data = ['component_id' => $component->component_id, 'quantity' => $qty];
        if ($customPrice) {
            $data['custom_price'] = [
                'pricing_scheme' => 'per_unit',
                'prices' => [
                    [
                        'starting_quantity' => 1,
                        'unit_price' => $customPrice,
                    ],
                ],
            ];
        }

        $data = array_merge($options, $data);

        maxio()->subscriptionComponent->updateQuantity(
            subscriptionId: $this->subscription->subscription_id,
            componentId: $component->component_id,
            options: $data
        );

        $maxioComponent = maxio()->subscriptionComponent
            ->list(subscriptionId: $this->subscription->subscription_id)
            ->where('component_id', $component->component_id)->first();

        $componentPrice = maxio()->componentPrice->list(['filter' => [
            'ids' => $maxioComponent->price_point_id,
            'type' => 'catalog,default,custom',
        ]]);

        SubscriptionComponent::updateOrCreate(
            [
                'subscription_id' => $this->subscription->subscription_id,
                'component_id' => $maxioComponent->component_id,
            ],
            [
                'component_handle' => $maxioComponent->component_handle,
                'component_price_handle' => $maxioComponent->price_point_handle,
                'component_price_id' => $maxioComponent->price_point_id,
                'subscription_component_price' => $componentPrice->first()->prices->first()->unit_price,
                'subscription_component_quantity' => $maxioComponent->allocated_quantity,
                'created_at' => $component->created_at,
                'updated_at' => $component->updated_at,
            ]
        );

        ProductStructure::setMainComponent(subscription: $this->subscription);

        return $this;
    }

    public function updateSubscriptionComponent(Component $component, int $customPrice = null, array $options = []): static
    {
        $data = ['component_id' => $component->component_id];
        $subscriptionComponent = SubscriptionComponent::where('component_id', $component->component_id)
            ->where('subscription_id', $this->subscription->subscription_id)->first();
        if ($customPrice) {
            $data['custom_price'] = [
                'pricing_scheme' => 'per_unit',
                'prices' => [
                    [
                        'starting_quantity' => 1,
                        'unit_price' => $customPrice / 100,
                    ],
                ],
            ];
            $data['quantity'] = $subscriptionComponent->subscription_component_quantity + 1;
        } else {
            $data['price_point_id'] = $component->price->component_price_id;
            $data['quantity'] = $subscriptionComponent->subscription_component_quantity;
        }

        $data = array_merge($options, $data);

        maxio()->subscriptionComponent->updateQuantity(
            subscriptionId: $this->subscription->subscription_id,
            componentId: $component->component_id,
            options: $data
        );

        $maxioComponent = maxio()->subscriptionComponent
            ->list(subscriptionId: $this->subscription->subscription_id)
            ->where('component_id', $component->component_id)->first();

        $componentPrice = maxio()->componentPrice->list(['filter' => [
            'ids' => $maxioComponent->price_point_id,
            'type' => 'catalog,default,custom',
        ]]);

        SubscriptionComponent::updateOrCreate(
            [
                'subscription_id' => $this->subscription->subscription_id,
                'component_id' => $maxioComponent->component_id,
            ],
            [
                'component_handle' => $maxioComponent->component_handle,
                'component_price_handle' => $maxioComponent->price_point_handle,
                'component_price_id' => $maxioComponent->price_point_id,
                'subscription_component_price' => $componentPrice->first()->prices->first()->unit_price,
                'subscription_component_quantity' => $maxioComponent->allocated_quantity,
                'created_at' => $component->created_at,
                'updated_at' => $component->updated_at,
            ]
        );

        return $this;
    }

    public function updateSubscriptionComponentQuantity(SubscriptionComponent $component, int $qty, array $options = []): static
    {
        $data = array_merge($options, ['quantity' => $qty]);

        $maxioSubscriptionComponent = maxio()->subscriptionComponent->updateQuantity(
            subscriptionId: $this->subscription->subscription_id,
            componentId: $component->component_id,
            options: $data
        );

        $component->update([
            'subscription_component_quantity' => $maxioSubscriptionComponent->quantity,
            'updated_at' => $maxioSubscriptionComponent->created_at,
        ]);

        return $this;
    }

    public function removeSubscriptionComponent(SubscriptionComponent $component, array $options = []): static
    {
        $data = array_merge($options, ['quantity' => 0]);
        $maxioSubscriptionComponent = maxio()->subscriptionComponent->updateQuantity(
            subscriptionId: $this->subscription->subscription_id,
            componentId: $component->component_id,
            options: $data
        );

        $component->update([
            'subscription_component_quantity' => $maxioSubscriptionComponent->quantity,
            'is_main_component' => null,
            'updated_at' => $maxioSubscriptionComponent->created_at,
        ]);

        return $this;
    }

    public function holdSubscription(): static
    {
        $maxioSubscription = maxio()->subscriptionStatus->hold(subscriptionId: $this->subscription->subscription_id);

        $this->updateSubscription($maxioSubscription);

        return $this;
    }

    public function holdSubscriptionUntil(string $date): static
    {
        $maxioSubscription = maxio()->subscriptionStatus->hold(subscriptionId: $this->subscription->subscription_id, until: $date);

        $this->updateSubscription($maxioSubscription);

        return $this;
    }

    public function resumeHoldSubscription(): static
    {
        $maxioSubscription = maxio()->subscriptionStatus->unHold(subscriptionId: $this->subscription->subscription_id);

        $this->updateSubscription($maxioSubscription);

        return $this;
    }

    public function cancelSubscriptionNow(): static
    {
        $maxioSubscription = maxio()->subscriptionStatus->cancelNow(subscriptionId: $this->subscription->subscription_id);

        $this->updateSubscription($maxioSubscription);

        return $this;
    }

    public function reactivateSubscription(): static
    {
        $maxioSubscription = maxio()->subscriptionStatus->reactivate(subscriptionId: $this->subscription->subscription_id);

        $this->updateSubscription($maxioSubscription);

        return $this;
    }

    public function cancelSubscription(): static
    {
        maxio()->subscriptionStatus->cancel(subscriptionId: $this->subscription->subscription_id);

        return $this;
    }

    public function resumeSubscription(): static
    {
        maxio()->subscriptionStatus->resume(subscriptionId: $this->subscription->subscription_id);

        return $this;
    }

    public function skipTrial()
    {
        $this->skipTrial = true;

        return $this;
    }

    public function subscriptionStatus()
    {
        return $this->subscription->status;
    }

    public function findMaxioSubscription()
    {
        return maxio()->subscription->find(subscriptionId: $this->subscription->subscription_id);
    }

    public function subscriptionPaymentMethod()
    {
        $sub = $this->findMaxioSubscription();

        if (isset($sub->credit_card)) {
            return $sub->credit_card->id;
        }

        return null;
    }

    protected function updateSubscription(ChargifyObject $maxioSubscription)
    {
        $this->subscription->forceFill([
            'status' => $maxioSubscription->state,
            'next_billing_at' => $maxioSubscription->next_assessment_at,
            'updated_at' => $maxioSubscription->updated_at,
        ])->save();
    }

    public function setDefaultPaymentMethods(string $paymentProfileId): ChargifyObject
    {
        return maxio()->paymentProfile->setDefault(paymentProfileId: $paymentProfileId, subscriptionId: $this->subscription->subscription_id);
    }

    protected function migrateProduct(array $data): static
    {
        /** @var Subscription $maxioSubscription * */
        $maxioSubscription = maxio()->subscription->migrateProduct(subscriptionId: $this->subscription->subscription_id, parameters: $data);

        $this->subscription->forceFill(
            [
                'product_price_handle' => $maxioSubscription->product->product_price_point_handle,
                'product_handle' => $maxioSubscription->product->handle,
                'status' => $maxioSubscription->state,
                'payment_collection_method' => $maxioSubscription->payment_collection_method,
                'subscription_interval' => SubscriptionInterval::getIntervalUnit($maxioSubscription->product->interval),
                'total_revenue_in_cents' => $maxioSubscription->total_revenue_in_cents,
                'product_price_in_cents' => $maxioSubscription->product_price_in_cents,
                'next_billing_at' => $maxioSubscription->next_assessment_at,
                'updated_at' => $maxioSubscription->updated_at,
            ]
        )->save();

        return $this;
    }
}
