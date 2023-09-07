<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class Subscription extends AbstractService
{
    public function create(array $parameters): array
    {
//        $this->validatePayload($parameters, [
//            'product_id' => 'required|integer',
//            'customer_id' => 'required|integer',
//        ]);

        return $this->getClient()
            ->request('subscriptions', 'post', ['subscription' => $parameters])
            ->json('subscription', []);
    }

    public function update(string $subscriptionId, array $parameters): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId, 'put', ['subscription' => $parameters])
            ->json('subscription', []);
    }

    public function override(string $subscriptionId, array $parameters): void
    {
        $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/override', 'put', ['subscription' => $parameters]);
    }

    public function getSubscriptionById(string $subscriptionId): array
    {
        $subscription = $this->getClient()
            ->request('subscriptions/'.$subscriptionId, 'get', ['include[]' => 'self_service_page_token'])
            ->json('subscription', []);

        if (isset($subscription['self_service_page_token'])) {
            $subscription['self_service_page_url'] =
                $this->getSelfServicePageUrl($subscription['id'], $subscription['self_service_page_token']);
        }

        return $subscription;
    }

    public function getSelfServicePageUrl(string $subscriptionId, string $pageToken): string
    {
        return 'https://'.$this->getConfig()->getSubdomain()
            .'.chargifypay.com/update_payment/'.$subscriptionId.'/'.$pageToken;
    }

    public function getSubscriptionByReference(string $reference): array
    {
        return $this->getClient()
            ->request('subscriptions/lookup', 'get', ['reference' => $reference])
            ->json('subscription', []);
    }

    public function addCouponsToSubscription(string $subscriptionId, array $codes): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/add_coupon', 'post', ['codes' => $codes])
            ->json('subscription', []);
    }

    public function removeCouponFromSubscription(string $subscriptionId, string $code): void
    {
        $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/remove_coupon', 'delete', ['coupon_code' => $code]);
    }

    public function activateSubscription(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/activate', 'put')
            ->json('subscription', []);
    }

    public function getSubscriptionPreview(array $parameters): array
    {
        return $this->getClient()
            ->request('subscriptions/preview', 'post', ['subscription' => $parameters])
            ->json('subscription_preview', []);
    }

    public function listSubscriptions(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
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

        return $this->getClient()
            ->request('subscriptions', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['subscription']);
    }

    public function migrateSubscriptionProduct(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'product_id' => 'required_without:product_price_point_id|integer',
            'product_price_point_id' => 'required_without:product_id|integer',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/migrations', 'post', ['migration' => $parameters])
            ->json('subscription', []);
    }

    public function migrateSubscriptionProductPreview(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'product_id' => 'required_without:product_price_point_id|integer',
            'product_price_point_id' => 'required_without:product_id|integer',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/migrations/preview', 'post', ['migration' => $parameters])
            ->json('migration', []);
    }
}
