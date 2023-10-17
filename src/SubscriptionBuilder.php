<?php

namespace WinLocalInc\Chjs;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Chargify\PricePoints;
use WinLocalInc\Chjs\Enums\ProductPricing;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;

class SubscriptionBuilder
{
    const DEFAULT_PAYMENT_COLLECTION_METHOD = 'automatic';

    const REMITTANCE_PAYMENT_COLLECTION_METHOD = 'remittance';

    const DEFAULT_TIMEZONE = 'EDT';

    protected ProductPrice $pricePoint;

    protected string $userId;

    protected ?string $trialEndedAt = null;

    protected ?string $token = null;

    protected ?string $paymentProfile = null;

    protected ?array $components = null;

    protected string $paymentCollectionMethod = self::DEFAULT_PAYMENT_COLLECTION_METHOD;

    /**
     * @throws Exception
     */
    public function __construct(protected Model $workspace, protected ProductPricing $productPricing)
    {
        $this->userId = $this->workspace->owner_id;
        $this->pricePoint = $this->getProductPriceByHandle($productPricing->value);
    }

    /**
     * @throws Exception
     */
    public function componentsFromArray($components): static
    {
        foreach ($components as $componentData) {
            if (isset($componentData['component_price_handle'])) {
                $componentPrice = $this->getComponentPriceByHandle($componentData['component_price_handle']);
                $this->component($componentPrice, $componentData['quantity']);
            } elseif (isset($componentData['component_handle'])) {
                $component = $this->getComponentByHandle($componentData['component_handle']);
                $this->customComponent($component, $componentData['quantity'], $componentData['customPrice']);
            }
        }

        return $this;
    }

    private function getProductPriceByHandle($handle): ProductPrice
    {
        try {
            return ProductPrice::where('product_price_handle', $handle)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new Exception("Product price handle not found: {$handle}");
        }
    }

    private function getComponentPriceByHandle($handle): ComponentPrice
    {
        try {
            return ComponentPrice::where('component_price_handle', $handle)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new Exception("Component price handle not found: {$handle}");
        }
    }

    private function getComponentByHandle($handle): Component
    {
        try {
            return Component::where('component_handle', $handle)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new Exception("Component handle not found: {$handle}");
        }
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

    public function customComponent(Component $component, int $quantity, int $customPrice): static
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
        $this->paymentCollectionMethod = self::REMITTANCE_PAYMENT_COLLECTION_METHOD;

        return $this;
    }

    public function trialDays(int $days): static
    {
        $this->trialEndedAt = Carbon::now()->addDays($days)->setTimezone(self::DEFAULT_TIMEZONE)->toW3cString();

        return $this;
    }

    public function trialDate(string $date): static
    {
        $this->trialEndedAt = Carbon::create($date.' '.date('H:i:s'))->setTimezone(self::DEFAULT_TIMEZONE)->toW3cString();

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
                $carry[$item->component_id] = $item->prices->first()->unit_price;

                return $carry;
            }, []);

            foreach ($components as $component) {
                SubscriptionComponent::updateOrCreate(
                    [
                        'subscription_id' => $subscription->subscription_id,
                        'component_id' => $component->component_id,
                    ],
                    [
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

    public function preview(): ChargifyObject
    {
        return maxio()->subscription->preview($this->formulateSubscriptionParameters());
    }

    protected function formulateSubscriptionParameters(): array
    {
        $parameters = [];

        if (empty($this->components)) {
            throw new InvalidArgumentException("can't create subscription without components");
        }

        $parameters['product_price_point_handle'] = $this->pricePoint->product_price_handle;

        $parameters['components'] = $this->components;

        $parameters['payment_collection_method'] = $this->paymentCollectionMethod;
        $parameters['product_handle'] = $this->pricePoint->product_handle;
        $parameters['customer_reference'] = $this->userId;
        $parameters['reference'] = $this->workspace->workspace_id;

        if ($this->paymentProfile) {
            $parameters['payment_profile_id'] = $this->paymentProfile;
        } elseif ($this->token) {
            $parameters['credit_card_attributes'] = [
                'chargify_token' => $this->token,
                'payment_type' => 'credit_card',
            ];
        }

        if ($this->trialEndedAt) {
            $parameters['next_billing_at'] = $this->trialEndedAt;
        }

        return $parameters;
    }
}
