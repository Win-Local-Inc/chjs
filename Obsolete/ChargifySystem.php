<?php

namespace Obsolete;

use App\Models\Chargify\ChargifyComponent;
use App\Models\Chargify\ChargifyComponentPricePoint;
use App\Models\Chargify\ChargifyCustomer;
use App\Models\Chargify\ChargifyPaymentProfile;
use App\Models\Chargify\ChargifySubscription;
use App\Models\Chargify\ChargifySubscriptionGroup;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ChargifySystem
{
    public function __construct(
        public Chargify $chargify
    ) {
    }

    public function upsertCustomerWithPaymentProfileFromToken(User $user, string $token, string $parentCustomerId = null): string
    {
        $options = $this->chargify->paymentProfile()->getPaymentProfileByToken($token);

        $costumerId = $this->upsertCustomer($user, $options, $parentCustomerId);

        $this->createPaymnetProfile($costumerId, $token);

        return $costumerId;
    }

    public function createPaymnetProfile(string $costumerId, string $token): void
    {
        $paymentProfile = $this->chargify->paymentProfile()->create($costumerId, $token);

        ChargifyPaymentProfile::where('chargify_customer_id', $costumerId)
            ->update(['is_default' => false]);

        ChargifyPaymentProfile::create([
            'id' => $paymentProfile['id'],
            'chargify_customer_id' => $costumerId,
            'masked_card_number' => $paymentProfile['masked_card_number'],
            'is_default' => true,
        ]);
    }

    public function upsertCustomer(User $user, array $options, string $parentCustomerId = null): string
    {
        $data = [
            'first_name' => $options['first_name'] ?? $user->firstname,
            'last_name' => $options['last_name'] ?? $user->lastname,
            'email' => $user->email,
        ];

        foreach ([
            'address' => 'billing_address',
            'address_2' => 'billing_address_2',
            'city' => 'billing_city',
            'state' => 'billing_state',
            'zip' => 'billing_zip',
            'country' => 'billing_country',
        ] as $key => $value) {
            if (isset($options[$value])) {
                $data[$key] = $options[$value];
            }
        }

        if ($parentCustomerId) {
            $data['parent_id'] = $parentCustomerId;
        }

        $customerResponse = $user->chargifyCustomer?->id ?
            $this->chargify->customer()->update($user->chargifyCustomer->id, $data) :
                $this->chargify->customer()->create($data);

        $customerId = $customerResponse['id'];

        if (! $user->chargifyCustomer?->id) {
            ChargifyCustomer::create([
                'id' => $customerId,
                'user_id' => $user->user_id,
            ]);
        }

        if ($parentCustomerId) {
            ChargifyCustomer::where([
                'id' => $customerId,
            ])->update(['parent_id' => $parentCustomerId]);
        }

        return $customerId;
    }

    public function attachCustomerToParent(string $parentCustomerId, string $childCostumerId): void
    {
        $this->chargify->customer()->update($childCostumerId, ['parent_id' => $parentCustomerId]);

        ChargifyCustomer::where([
            'id' => $childCostumerId,
        ])->update(['parent_id' => $parentCustomerId]);
    }

    public function detachCustomerFromParent(string $childCostumerId): void
    {
        $this->chargify->customer()->update($childCostumerId, ['parent_id' => null]);

        ChargifyCustomer::where([
            'id' => $childCostumerId,
        ])->update(['parent_id' => null]);
    }

    public function createSubscription(
        User $user,
        string $workspaceId,
        array $product,
        array $metaFields = null,
        array $components = null,
        array $coupons = null,
        string $groupId = null,
    ): string {
        $group = $groupId ? ChargifySubscriptionGroup::findOrFail($groupId) : null;

        $data = [
            'customer_id' => $user->chargifyCustomer->id,
            'product_id' => $product['product_id'],
        ];

        if (isset($product['product_price_point_id'])) {
            $data['product_price_point_id'] = $product['product_price_point_id'];
        } elseif (isset($product['custom_price'])) {
            $data['custom_price'] = $product['custom_price'];
        }

        if (null !== ($paymentProfile = $user->chargifyCustomer->defaultPaymentProfile()?->id ?? null)) {
            $data['payment_profile_id'] = $paymentProfile;
        }

        if ($group) {
            $data['group'] = [
                'target' => [
                    'type' => 'subscription',
                    'id' => $group->primary_subscription_id,
                ],
            ];
        }

        if (is_array($components) && count($components)) {
            $data['components'] = $components;
        }

        if (is_array($coupons) && count($coupons)) {
            $data['coupon_codes'] = array_column($coupons, 'coupon_code');
        }

        if (is_array($metaFields) && count($metaFields)) {
            $data['metafields'] = $metaFields;
        }

        $response = $this->chargify->subscription()->create($data);

        $lock = Cache::lock('chargify_subscription_'.$response['id'], 3600);
        try {
            $lock->block(3600);

            $subscription = ChargifySubscription::firstOrNew([
                'id' => $response['id'],
            ]);

            // if custom_price will be updated from hook
            if (isset($product['product_price_point_id'])) {
                $subscription->chargify_product_price_point_id = $product['product_price_point_id'];
            }
            $subscription->user_id = $user->user_id;
            $subscription->workspace_id = $workspaceId;
            $subscription->state = $response['state'];
            $subscription->balance_in_cents = $response['balance_in_cents'];
            $subscription->total_revenue_in_cents = $response['total_revenue_in_cents'];
            $subscription->product_price_in_cents = $response['product_price_in_cents'];
            $subscription->current_period_ends_at = ChargifyUtility::getFixedDateTime($response['current_period_ends_at']);
            $subscription->trial_ended_at = ChargifyUtility::getFixedDateTime($response['trial_ended_at']);
            $subscription->created_at = ChargifyUtility::getFixedDateTime($response['created_at']);
            $subscription->updated_at = ChargifyUtility::getFixedDateTime($response['updated_at']);

            if ($group) {
                $subscription->chargify_subscription_group_id = $group->id;
            }

            $subscription->save();

            if (is_array($components) && count($components)) {
                // if custom_price will be updated from hook
                $componentPricePointIds = array_column($components, 'price_point_id');

                $subscription->componentPricePoints()->syncWithoutDetaching($componentPricePointIds);
            }

            if (is_array($coupons) && count($coupons)) {
                $couponIds = array_column($coupons, 'coupon_id');

                $subscription->coupons()->syncWithoutDetaching($couponIds);
            }
        } finally {
            $lock?->release();
        }

        return $response['id'];
    }

    public function createSubscriptionGroup(string $costumerId, string $primarySubscriptionId, array $subscriptionIds = []): string
    {
        $this->chargify->subscriptionGroup()->create([
            'subscription_id' => $primarySubscriptionId,
            'member_ids' => $subscriptionIds,
        ]);

        $groupInfo = $this->chargify->subscriptionGroup()->getGroupBySubscriptionId($primarySubscriptionId);

        $group = ChargifySubscriptionGroup::create([
            'id' => $groupInfo['uid'],
            'chargify_customer_id' => $costumerId,
            'primary_subscription_id' => $primarySubscriptionId,
        ]);

        ChargifySubscription::whereIn('id', array_merge([$primarySubscriptionId], $subscriptionIds))
            ->update(['chargify_subscription_group_id' => $group->id]);

        return $group->id;
    }

    public function removeSubscriptionGroup(string $groupId): void
    {
        $group = ChargifySubscriptionGroup::findOrFail($groupId);

        $this->chargify->subscriptionGroup()->update($group->id, ['member_ids' => []]);
        $this->chargify->subscriptionGroup()->delete($group->id);

        ChargifySubscription::where('chargify_subscription_group_id', $group->id)
            ->update(['chargify_subscription_group_id' => null]);

        $group->delete();
    }

    public function attachSubscriptionToGroup(string $groupId, string $subscriptionId): void
    {
        $group = ChargifySubscriptionGroup::findOrFail($groupId);

        $this->chargify->subscriptionGroup()->addSubscriptionToGroup($subscriptionId, [
            'target' => [
                'type' => 'subscription',
                'id' => $group->primary_subscription_id,
            ],
        ]);

        ChargifySubscription::where('id', $subscriptionId)
            ->update(['chargify_subscription_group_id' => $group->id]);
    }

    public function detachSubscriptionFromGroup(string $subscriptionId): void
    {
        $this->chargify->subscriptionGroup()->removeSubscriptionFromGroup($subscriptionId);

        ChargifySubscription::where('id', $subscriptionId)
            ->update(['chargify_subscription_group_id' => null]);
    }

    public function updateSubscriptionComponents(string $subscriptionId): void
    {
        $componentsResponse = $this->chargify->subscriptionComponent()
            ->listSubscriptionComponents($subscriptionId, [
                'price_point_ids' => 'not_null',
            ]);

        if ($componentsResponse->isEmpty()) {

            ChargifySubscription::find($subscriptionId)
                ->componentPricePoints()
                ->detach();

            return;
        }

        $upsertComponents = $componentsResponse->map(function ($component) {
            return [
                'id' => $component['component_id'],
                'chargify_product_family_id' => $component['product_family_id'],
                'name' => $component['name'],
                'handle' => $component['component_handle'],
                'unit_name' => $component['unit_name'],
                'kind' => $component['kind'],
                'created_at' => ChargifyUtility::getFixedDateTime($component['created_at']),
                'updated_at' => ChargifyUtility::getFixedDateTime($component['updated_at']),
            ];
        })
            ->toArray();

        ChargifyComponent::upsert($upsertComponents, ['id']);

        $componentPricePoints = $this->chargify->component()
            ->listPricePoints([
                'filter' => [
                    'ids' => $componentsResponse->implode('price_point_id', ','),
                    'type' => 'catalog,custom,default',
                ],
            ]);

        $upsertComponenPricepoints = $componentPricePoints->map(function ($componentPricePoint) {
            return [
                'id' => $componentPricePoint['id'],
                'chargify_component_id' => $componentPricePoint['component_id'],
                'name' => $componentPricePoint['name'],
                'handle' => $componentPricePoint['handle'],
                'type' => $componentPricePoint['type'],
                'pricing_scheme' => $componentPricePoint['pricing_scheme'],
                'prices' => json_encode($componentPricePoint['prices']),
                'archived_at' => ChargifyUtility::getFixedDateTime($componentPricePoint['archived_at']),
                'created_at' => ChargifyUtility::getFixedDateTime($componentPricePoint['created_at']),
                'updated_at' => ChargifyUtility::getFixedDateTime($componentPricePoint['updated_at']),
            ];
        })
            ->toArray();

        ChargifyComponentPricePoint::upsert($upsertComponenPricepoints, ['id']);

        ChargifySubscription::find($subscriptionId)
            ->componentPricePoints()
            ->sync($componentsResponse->pluck('price_point_id'));
    }
}
