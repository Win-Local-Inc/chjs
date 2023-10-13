<?php

namespace WinLocalInc\Chjs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use WinLocalInc\Chjs\Chargify\PricePoints;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;

class SubscriptionBuilder
{
    protected ProductPrice $pricePoint;

    protected string $userId;

    protected ?string $nextBillingAt = null;

    protected ?string $trialEndedAt = null;

    protected ?string $token = null;

    protected ?string $paymentProfile = null;

    protected ?array $customPrice = null;

    protected ?array $components = null;

    protected string $paymentCollectionMethod = 'automatic';

    public function __construct(protected Model $workspace)
    {
        $this->userId = $this->workspace->owner_id;
    }

    public function price(ProductPrice $pricePoint): static
    {
        $this->pricePoint = $pricePoint;

        return $this;
    }

    public function customPrice(ProductPrice $pricePoint, int $customPrice = null): static
    {
        $this->pricePoint = $pricePoint;

        $this->customPrice['price_in_cents'] = $customPrice;
        $this->customPrice['interval'] = $pricePoint->product_price_interval->getInterval();
        $this->customPrice['interval_unit'] = 'month';

        return $this;
    }

    public function component(ComponentPrice $componentPrice, int $quantity = 1): static
    {
        $component = [
            'component_id' => $componentPrice->component_id,
            'allocated_quantity' => $quantity,
            'price_point_id' => $componentPrice->product_price_id,
        ];

        $this->components[] = $component;

        return $this;
    }

    public function customComponent(Component $component, int $quantity = 1, int $customPrice = null): static
    {
        $component = [
            'component_id' => $component->component_id,
            'allocated_quantity' => $quantity,
            'custom_price' => [
                'pricing_scheme' => 'per_unit',
                'prices' => [
                    [
                        'starting_quantity' => 1,
                        'unit_price' => $customPrice / 100,
                    ],
                ],
            ],
        ];

        $this->components[] = $component;

        return $this;
    }

    public function paymentProfile($id): static
    {
        $this->paymentProfile = $id;

        return $this;
    }

    public function token(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function remittance(): static
    {
        $this->paymentCollectionMethod = 'remittance';

        return $this;
    }

    public function trialDays(int $days): static
    {
        $this->trialEndedAt = Carbon::now()->addDays($days)->setTimezone('EDT')->toW3cString();

        return $this;
    }

    public function trialDate(string $date): static
    {
        $this->trialEndedAt = Carbon::create($date.' '.date('H:i:s'))->setTimezone('EDT')->toW3cString();

        return $this;
    }

    public function create()
    {
        $subscriptionMaxio = maxio()->subscription->create($this->formulateSubscriptionParameters());

        $subscription = Subscription::create(
            [
                'subscription_id' => $subscriptionMaxio->id,
                'workspace_id' => $this->workspace->workspace_id,
                'user_id' => $this->workspace->owner->user_id,
                'product_id' => $this->pricePoint->product_id,
                'product_handle' => $this->pricePoint->product->product_handle,
                'status' => $subscriptionMaxio->state,
                'payment_collection_method' => $subscriptionMaxio->payment_collection_method,
                'subscription_interval' => $this->pricePoint->product_price_interval,
                'total_revenue_in_cents' => $subscriptionMaxio->total_revenue_in_cents,
                'next_billing_at' => $subscriptionMaxio->next_assessment_at,
                'created_at' => $subscriptionMaxio->created_at,
                'updated_at' => $subscriptionMaxio->updated_at,
            ]
        );

        if ($this->components) {
            $components = maxio()->subscriptionComponent->list($subscriptionMaxio->id);
            $pricePoints = $components->pluck('price_point_id')->toArray();

            $componentPrices = maxio()->componentPrice->list(['filter' => ['ids' => implode(',', $pricePoints)]]);

            $pricesMap = $componentPrices->reduce(function (array $carry, PricePoints $item) {
                $carry[$item->price_point->component_id] = $item->price_point->prices->first()->unit_price;

                return $carry;
            }, []);

            foreach ($components as $component) {
                SubscriptionComponent::create(
                    [
                        'subscription_id' => $subscription->subscription_id,
                        'component_id' => $component->component_id,
                        'component_handle' => $component->component_handle,
                        'component_price_handle' => $component->price_point_handle,
                        'component_price_id' => $component->price_point_id,
                        'subscription_component_price' => $pricesMap[$component->component_id],
                        'subscription_component_quantity' => $component->allocated_quantity,
                        'is_main_component' => ProductStructure::isMainComponent(product: $this->pricePoint->product->product_handle, component: $component->component_handle),
                        'created_at' => $component->created_at,
                        'updated_at' => $component->updated_at,
                    ]
                );
            }

        }

        return $subscription;

    }

    public function preview()
    {
        return maxio()->subscription->preview($this->formulateSubscriptionParameters());
    }

    protected function formulateSubscriptionParameters(): array
    {
        $parameters = [];

        if (empty($this->components)) {
            throw new InvalidArgumentException("can't create subscription without components");
        }

        if ($this->customPrice) {
            $parameters['custom_price'] = $this->customPrice;
        } else {
            $parameters['product_price_point_handle'] = $this->pricePoint->product_price_handle;
        }

        $parameters['components'] = $this->components;

        $parameters['payment_collection_method'] = $this->paymentCollectionMethod;
        $parameters['product_handle'] = $this->pricePoint->product_handle;
        $parameters['customer_reference'] = $this->userId;
        $parameters['reference'] = $this->workspace->workspace_id;

        if ($this->token) {
            $parameters['credit_card_attributes'] = [
                'chargify_token' => $this->token,
                'payment_type' => 'credit_card',
            ];
        }

        if ($this->trialEndedAt) {
            $parameters['next_billing_at'] = $this->trialEndedAt;
        }

        if ($this->paymentProfile) {
            $parameters['payment_profile_id'] = $this->paymentProfile;
        }

        return $parameters;
    }
}
