<?php

namespace Obsolete;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Obsolete\Chargify\Services\ApiExport apiExport()
 * @method static \Obsolete\Chargify\Services\Component component()
 * @method static \Obsolete\Chargify\Services\Coupon coupon()
 * @method static \Obsolete\Chargify\Services\Customer customer()
 * @method static \Obsolete\Chargify\Services\CustomFields customFields()
 * @method static \Obsolete\Chargify\Services\Insight insight()
 * @method static \Obsolete\Chargify\Services\Invoice invoice()
 * @method static \Obsolete\Chargify\Services\Offer offer()
 * @method static \Obsolete\Chargify\Services\PaymentProfile paymentProfile()
 * @method static \Obsolete\Chargify\Services\Product product()
 * @method static \Obsolete\Chargify\Services\ProductFamily productFamily()
 * @method static \Obsolete\Chargify\Services\ProductPricePoint productPricePoint()
 * @method static \Obsolete\Chargify\Services\ProformaInvoice proformaInvoice()
 * @method static \Obsolete\Chargify\Services\Subscription subscription()
 * @method static \Obsolete\Chargify\Services\SubscriptionComponent subscriptionComponent()
 * @method static \Obsolete\Chargify\Services\SubscriptionGroup subscriptionGroup()
 * @method static \Obsolete\Chargify\Services\SubscriptionGroupInvoiceAccount subscriptionGroupInvoiceAccount()
 * @method static \Obsolete\Chargify\Services\SubscriptionGroupStatus subscriptionGroupStatus()
 * @method static \Obsolete\Chargify\Services\SubscriptionInvoiceAccount subscriptionInvoiceAccount()
 * @method static \Obsolete\Chargify\Services\SubscriptionNote subscriptionNote()
 * @method static \Obsolete\Chargify\Services\SubscriptionStatus subscriptionStatus()
 *
 * @see \Obsolete\Chargify\Chargify
 */
class ChargifyFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Chargify::class;
    }
}
