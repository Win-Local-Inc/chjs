<?php

namespace Obsolete\WebhookHandlers;

use App\Models\Chargify\ChargifyCustomer;
use App\Models\Chargify\ChargifyPaymentProfile;
use App\Models\Chargify\ChargifyProduct;
use App\Models\Chargify\ChargifyProductFamily;
use App\Models\Chargify\ChargifyProductPricePoint;
use App\Models\Chargify\ChargifySubscription;
use App\Models\Chargify\ChargifySubscriptionGroup;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Obsolete\Attributes\HandleEvents;
use Obsolete\ChargifyUtility;
use Obsolete\Enums\WebhookEvents;

#[HandleEvents(
    WebhookEvents::PaymentSuccess,
    WebhookEvents::SignupSuccess,
    WebhookEvents::RenewalSuccess,
    WebhookEvents::PaymentFailure,
    WebhookEvents::SignupFailure,
    WebhookEvents::RenewalFailure,
    WebhookEvents::DunningStepReached,
    WebhookEvents::BillingDateChange,
    WebhookEvents::SubscriptionStateChange,
    WebhookEvents::DelayedSubscriptionCreationSuccess
)]
class SubscriptionEvents extends AbstractHandler
{
    protected function handleEvent(array $payload)
    {
        $this->validateData($payload);
        $data = $payload['subscription'];
        $lock = Cache::lock('chargify_subscription_'.$data['id'], 3600);
        try {
            $lock->block(3600);

            $this->updateProduct($data);
            $this->updateCustomer($data);
            $this->updateSubscription($data);
            $this->updateGroup($data);
            $this->updateComponents($data);

        } finally {
            $lock?->release();
        }
    }

    protected function validateData(array &$payload): void
    {
        Validator::make($payload, [
            'subscription' => 'required|array',
            'subscription.id' => 'required|integer',
        ])->validate();
    }

    protected function updateCustomer(array &$data): void
    {
        if (! array_key_exists('customer', $data) ||
            ! array_key_exists('id', $data['customer']) ||
            ! array_key_exists('email', $data['customer'])) {
            return;
        }

        $customerData = $data['customer'];

        $customer = ChargifyCustomer::find($customerData['id']);
        if (! $customer) {
            $user = User::where('email', $customerData['email'])->first();
            if (! $user) {
                Log::notice(
                    'Chargify Webhook CustomerUpsert User Not Exists: '.$customerData['email'],
                    ['event_id' => $this->chargifyEvent->id]
                );

                return;
            }
            ChargifyCustomer::insertOrIgnore([
                'id' => $customerData['id'],
                'user_id' => $user->user_id,
                'created_at' => ChargifyUtility::getFixedDateTime($customerData['created_at']),
                'updated_at' => ChargifyUtility::getFixedDateTime($customerData['updated_at']),
            ]);
            $customer = ChargifyCustomer::find($customerData['id']);
        }

        if (array_key_exists('parent_id', $customerData) &&
            $customer->parent_id !== $customerData['parent_id']) {
            $customer->update(['parent_id' => $customerData['parent_id']]);
        }

        if (array_key_exists('credit_card', $data) &&
            array_key_exists('id', $data['credit_card']) &&
            array_key_exists('customer_id', $data['credit_card']) &&
            $customerData['id'] === $data['credit_card']['customer_id'] &&
            ! ChargifyPaymentProfile::find($data['credit_card']['id'])) {

            ChargifyPaymentProfile::where('chargify_customer_id', $customerData['id'])
                ->update(['is_default' => false]);

            ChargifyPaymentProfile::insertOrIgnore([
                'id' => $data['credit_card']['id'],
                'chargify_customer_id' => $customerData['id'],
                'masked_card_number' => $data['credit_card']['masked_card_number'],
                'is_default' => true,
                'created_at' => ChargifyUtility::getFixedDateTime($data['created_at']),
                'updated_at' => ChargifyUtility::getFixedDateTime($data['updated_at']),
            ]);
        }
    }

    protected function updateSubscription(array &$data): void
    {
        $subscription = ChargifySubscription::firstOrNew([
            'id' => $data['id'],
        ]);

        $subscription->chargify_product_price_point_id = $data['product_price_point_id'];
        $subscription->state = $data['state'];
        $subscription->balance_in_cents = $data['balance_in_cents'];
        $subscription->total_revenue_in_cents = $data['total_revenue_in_cents'];
        $subscription->product_price_in_cents = $data['product_price_in_cents'];
        $subscription->current_period_ends_at = ChargifyUtility::getFixedDateTime($data['current_period_ends_at']);
        $subscription->trial_ended_at = ChargifyUtility::getFixedDateTime($data['trial_ended_at']);
        $subscription->created_at = ChargifyUtility::getFixedDateTime($data['created_at']);
        $subscription->updated_at = ChargifyUtility::getFixedDateTime($data['updated_at']);

        if (! $subscription->user_id && ($customer = ChargifyCustomer::find($data['customer']['id']))) {
            $subscription->user_id = $customer->user->user_id;
        }

        /**
         * TODO update workspace_id probably from meta fields  ChargifyFacade::customFields()->listMetadata($data['id'])
         * */
        $subscription->save();
    }

    protected function updateGroup(array &$data): void
    {
        if (! array_key_exists('group', $data) ||
            ! is_array($data['group']) ||
            ! array_key_exists('uid', $data['group'])) {
            return;
        }

        $primarySubscription = ChargifySubscription::find($data['group']['primary_subscription_id']);
        if (! $primarySubscription) {
            return;
        }

        ChargifySubscriptionGroup::updateOrInsert([
            'id' => $data['group']['uid'],
        ], [
            'primary_subscription_id' => $primarySubscription->id,
            'chargify_customer_id' => $primarySubscription->user->chargifyCustomer->id,
        ]);

        ChargifySubscription::whereIn('id', [$data['id'], $primarySubscription->id])
            ->update(['chargify_subscription_group_id' => $data['group']['uid']]);
    }

    protected function updateProduct(array &$data): void
    {
        if (! array_key_exists('product', $data) ||
            ! array_key_exists('product_family', $data['product'])
        ) {
            return;
        }

        $productInfo = $data['product'];
        $productFamilyInfo = $productInfo['product_family'];

        ChargifyProductFamily::upsert([[
            'id' => $productFamilyInfo['id'],
            'name' => $productFamilyInfo['name'],
            'handle' => $productFamilyInfo['handle'],
            'description' => $productFamilyInfo['description'],
            'created_at' => ChargifyUtility::getFixedDateTime($productFamilyInfo['created_at']),
            'updated_at' => ChargifyUtility::getFixedDateTime($productFamilyInfo['updated_at']),
        ]], ['id']);

        ChargifyProduct::upsert([[
            'id' => $productInfo['id'],
            'chargify_product_family_id' => $productFamilyInfo['id'],
            'name' => $productInfo['name'],
            'handle' => $productInfo['handle'],
            'description' => $productInfo['description'],
            'require_credit_card' => $productInfo['require_credit_card'] === 'true',
            'created_at' => ChargifyUtility::getFixedDateTime($productInfo['created_at']),
            'updated_at' => ChargifyUtility::getFixedDateTime($productInfo['updated_at']),
        ]], ['id']);

        ChargifyProductPricePoint::upsert([[
            'id' => $productInfo['product_price_point_id'],
            'chargify_product_id' => $productInfo['id'],
            'name' => $productInfo['product_price_point_name'],
            'price_in_cents' => $productInfo['price_in_cents'],
            'interval' => $productInfo['interval'],
            'interval_unit' => $productInfo['interval_unit'],
            'trial_price_in_cents' => $productInfo['trial_price_in_cents'],
            'trial_interval' => $productInfo['trial_interval'],
            'trial_interval_unit' => $productInfo['trial_interval_unit'],
            'archived_at' => ChargifyUtility::getFixedDateTime($productInfo['archived_at']),
        ]], ['id']);
    }

    protected function updateComponents(array &$data)
    {
        if (in_array($this->chargifyEvent->event_name, [
            WebhookEvents::PaymentSuccess->value,
            WebhookEvents::SignupSuccess->value,
            WebhookEvents::DelayedSubscriptionCreationSuccess->value,
        ])) {
            $this->getChargifySystem()->updateSubscriptionComponents($data['id']);
        }
    }
}
/**
 * "subscription": {
 *           "id": "66325710",
 *           "state": "active",
 *           "trial_started_at": null,
 *           "trial_ended_at": null,
 *           "activated_at": "2023-07-05 08:06:34 -0400",
 *           "created_at": "2023-07-05 08:06:32 -0400",
 *           "updated_at": "2023-07-05 08:06:34 -0400",
 *           "expires_at": null,
 *           "balance_in_cents": "0",
 *           "current_period_ends_at": "2023-08-05 08:06:32 -0400",
 *           "next_assessment_at": "2023-08-05 08:06:32 -0400",
 *           "canceled_at": null,
 *           "cancellation_message": null,
 *           "next_product_id": null,
 *           "next_product_handle": null,
 *           "cancel_at_end_of_period": "false",
 *           "payment_collection_method": "automatic",
 *           "snap_day": null,
 *           "cancellation_method": null,
 *           "current_period_started_at": "2023-07-05 08:06:32 -0400",
 *           "previous_state": "active",
 *           "signup_payment_id": "880402202",
 *           "signup_revenue": "100.00",
 *           "delayed_cancel_at": null,
 *           "coupon_code": null,
 *           "total_revenue_in_cents": "10000",
 *           "product_price_in_cents": "10000",
 *           "product_version_number": "1",
 *           "payment_type": "credit_card",
 *           "referral_code": null,
 *           "coupon_use_count": null,
 *           "coupon_uses_allowed": null,
 *           "reason_code": null,
 *           "automatically_resume_at": null,
 *           "offer_id": null,
 *           "payer_id": "66880641",
 *           "receives_invoice_emails": null,
 *           "product_price_point_id": "2366234",
 *           "next_product_price_point_id": null,
 *           "credit_balance_in_cents": "0",
 *           "prepayment_balance_in_cents": "0",
 *           "net_terms": null,
 *           "stored_credential_transaction_id": null,
 *           "locale": null,
 *           "reference": null,
 *           "currency": "USD",
 *           "on_hold_at": null,
 *           "scheduled_cancellation_at": null,
 *           "product_price_point_type": "default",
 *           "dunning_communication_delay_enabled": "false",
 *           "dunning_communication_delay_time_zone": null,
 *           "customer": {
 *               "id": "67973704",
 *               "first_name": "Wojtek",
 *               "last_name": "Kolanko",
 *               "organization": "wojtekkolanko",
 *               "email": "wojtekkolanko@op.lp",
 *               "created_at": "2023-07-05 07:40:05 -0400",
 *               "updated_at": "2023-07-05 08:03:50 -0400",
 *               "reference": null,
 *               "address": "3001 Summer",
 *               "address_2": "St",
 *               "city": "Stamford",
 *               "state": "CT",
 *               "state_name": "Connecticut",
 *               "zip": "06905",
 *               "country": "US",
 *               "country_name": "United States",
 *               "phone": "555-555-1212",
 *               "portal_invite_last_sent_at": null,
 *               "portal_invite_last_accepted_at": null,
 *               "verified": "false",
 *               "portal_customer_created_at": null,
 *               "vat_number": null,
 *               "cc_emails": null,
 *               "tax_exempt": "false",
 *               "parent_id": "66880641",
 *               "locale": null
 *           },
 *           "product": {
 *               "id": "6491993",
 *               "name": "Gold Plan",
 *               "handle": "gold",
 *               "description": "This is our gold plan.",
 *               "accounting_code": "123",
 *               "request_credit_card": "true",
 *               "expiration_interval": null,
 *               "expiration_interval_unit": null,
 *               "created_at": "2023-05-23 02:43:20 -0400",
 *               "updated_at": "2023-05-23 02:43:20 -0400",
 *               "price_in_cents": "10000",
 *               "interval": "1",
 *               "interval_unit": "month",
 *               "initial_charge_in_cents": null,
 *               "trial_price_in_cents": null,
 *               "trial_interval": null,
 *               "trial_interval_unit": null,
 *               "archived_at": null,
 *               "require_credit_card": "true",
 *               "return_params": null,
 *               "taxable": "false",
 *               "update_return_url": null,
 *               "tax_code": "D0000000",
 *               "initial_charge_after_trial": "false",
 *               "version_number": "1",
 *               "update_return_params": null,
 *               "default_product_price_point_id": "2366234",
 *               "request_billing_address": "false",
 *               "require_billing_address": "false",
 *               "require_shipping_address": "false",
 *               "use_site_exchange_rate": "true",
 *               "item_category": null,
 *               "product_price_point_id": "2366234",
 *               "product_price_point_name": "Original",
 *               "product_price_point_handle": "uuid:07c0d2c0-db63-013b-e8f2-0a575a757c23",
 *               "product_family": {
 *                   "id": "2541971",
 *                   "name": "Amazing product",
 *                   "description": "Amazing project management tool",
 *                   "handle": "amazing-product",
 *                   "accounting_code": null,
 *                   "created_at": "2023-05-23 02:42:19 -0400",
 *                   "updated_at": "2023-05-23 02:42:19 -0400"
 *               },
 *               "public_signup_pages": {
 *                   "id": "485158",
 *                   "return_url": null,
 *                   "return_params": null,
 *                   "url": "https://win-local.chargifypay.com/subscribe/kcrpb5nm45h2/gold",
 *                   "enabled": "true",
 *                   "nickname": null,
 *                   "currency": "USD"
 *               }
 *           },
 *           "credit_card": {
 *               "id": "52514769",
 *               "first_name": "Wojtek",
 *               "last_name": "Kolanko",
 *               "masked_card_number": "XXXX-XXXX-XXXX-1111",
 *               "card_type": "visa",
 *               "expiration_month": "12",
 *               "expiration_year": "2024",
 *               "customer_id": "67973704",
 *               "current_vault": "bogus",
 *               "vault_token": "1",
 *               "billing_address": "3001 Summer",
 *               "billing_city": "Stamford",
 *               "billing_state": "CT",
 *               "billing_zip": "06905",
 *               "billing_country": "US",
 *               "customer_vault_token": null,
 *               "billing_address_2": "St",
 *               "payment_type": "credit_card",
 *               "disabled": "false",
 *               "site_gateway_setting_id": null,
 *               "gateway_handle": null
 *           },
 *           "group": {
 *               "uid": "grp_b5dzj6rb7dgr6",
 *               "scheme": "1",
 *               "primary_subscription_id": "65304189",
 *               "primary": "false"
 *           }
 *       }
 */
