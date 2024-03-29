<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;
use WinLocalInc\Chjs\Chargify\ObjectTypes;
use WinLocalInc\Chjs\Chargify\Preview;

class SubscriptionService extends AbstractService
{
    public function create(array $parameters): ChargifyObject
    {
        return $this->post('subscriptions', ['subscription' => $parameters]);
    }

    public function update(string $subscriptionId, array $parameters): ChargifyObject
    {
        return $this->put('subscriptions/'.$subscriptionId, ['subscription' => $parameters]);
    }

    public function override(string $subscriptionId, array $parameters): void
    {
        $this->put('subscriptions/'.$subscriptionId.'/override', ['subscription' => $parameters]);
    }

    public function find(string $subscriptionId): ChargifyObject
    {
        $subscription = $this->get('subscriptions/'.$subscriptionId, ['include[]' => 'self_service_page_token']);

        if (isset($subscription->self_service_page_token)) {
            $subscription->self_service_page_url =
                $this->getSelfServicePageUrl($subscription->id, $subscription->self_service_page_token);
        }

        return $subscription;
    }

    public function getSelfServicePageUrl(string $subscriptionId, string $pageToken): string
    {
        return 'https://'.config('chjs.subdomain')
            .'.chargifypay.com/update_payment/'.$subscriptionId.'/'.$pageToken;
    }

    public function findByReference(string $reference): ChargifyObject
    {
        return $this->get('subscriptions/lookup', ['reference' => $reference]);
    }

    public function attachCoupons(string $subscriptionId, array $codes): ChargifyObject
    {
        return $this->post('subscriptions/'.$subscriptionId.'/add_coupon', ['codes' => $codes]);
    }

    public function detachCoupon(string $subscriptionId, string $code): void
    {
        $this->delete('subscriptions/'.$subscriptionId.'/remove_coupon', ['coupon_code' => $code]);
    }

    //Only trialing or awaiting signup subscriptions can be activated immediately
    public function activate(string $subscriptionId): ChargifyObject
    {
        return $this->put('subscriptions/'.$subscriptionId.'/activate');
    }

    public function preview(array $parameters): ChargifyObject
    {
        $response = ['preview' => $this->post('subscriptions/preview', ['subscription' => $parameters], true)];

        return ObjectTypes::resolve($response, Preview::class);
    }

    public function list(array $options = []): Collection
    {
        $this->validatePayload($options, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'date_field' => 'sometimes|string|in:current_period_ends_at,current_period_starts_at,created_at,activated_at,canceled_at,expires_at,trial_started_at,trial_ended_at,updated_at',
            'state' => 'sometimes|string|in:active,canceled,expired,expired_cards,on_hold,past_due,pending_cancellation,pending_renewal,suspended,trial_ended,trialing,unpaid',
            'direction' => 'sometimes|string|in:asc,desc',
            'sort' => 'sometimes|string|in:signup_date,period_start,period_end,next_assessment,updated_at,created_at',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'coupon' => 'sometimes|integer',
            'product' => 'sometimes|integer',
            'product_price_point_id' => 'sometimes|integer',
            'metadata' => 'sometimes|array',
        ]);

        return $this->get('subscriptions', array_merge(['per_page' => 100], $options));
    }

    public function migrateProduct(string $subscriptionId, array $parameters): ChargifyObject
    {
        $parameters = array_merge([
            'preserve_period' => true,
            'include_trial' => false,
            'include_initial_charge' => false,
        ], $parameters);

        return $this->post('subscriptions/'.$subscriptionId.'/migrations', ['migration' => $parameters]);
    }

    public function migrateProductPreview(string $subscriptionId, array $parameters): ChargifyObject
    {
        return $this->post('subscriptions/'.$subscriptionId.'/migrations/preview', ['migration' => $parameters]);
    }
}
