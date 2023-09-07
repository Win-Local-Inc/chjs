<?php

namespace Obsolete;

use Obsolete\Interfaces\ChargifyHttpClientInterface;
use Obsolete\Interfaces\ChargifyServiceFactoryInterface;

/**
 * @method \Obsolete\Chargify\Services\ApiExport apiExport()
 * @method \Obsolete\Chargify\Services\Component component()
 * @method \Obsolete\Chargify\Services\Coupon coupon()
 * @method \Obsolete\Chargify\Services\Customer customer()
 * @method \Obsolete\Chargify\Services\CustomFields customFields()
 * @method \Obsolete\Chargify\Services\Insight insight()
 * @method \Obsolete\Chargify\Services\Invoice invoice()
 * @method \Obsolete\Chargify\Services\Offer offer()
 * @method \Obsolete\Chargify\Services\PaymentProfile paymentProfile()
 * @method \Obsolete\Chargify\Services\Product product()
 * @method \Obsolete\Chargify\Services\ProductFamily productFamily()
 * @method \Obsolete\Chargify\Services\ProductPricePoint productPricePoint()
 * @method \Obsolete\Chargify\Services\ProformaInvoice proformaInvoice()
 * @method \Obsolete\Chargify\Services\Subscription subscription()
 * @method \Obsolete\Chargify\Services\SubscriptionComponent subscriptionComponent()
 * @method \Obsolete\Chargify\Services\SubscriptionGroup subscriptionGroup()
 * @method \Obsolete\Chargify\Services\SubscriptionGroupInvoiceAccount subscriptionGroupInvoiceAccount()
 * @method \Obsolete\Chargify\Services\SubscriptionGroupStatus subscriptionGroupStatus()
 * @method \Obsolete\Chargify\Services\SubscriptionInvoiceAccount subscriptionInvoiceAccount()
 * @method \Obsolete\Chargify\Services\SubscriptionNote subscriptionNote()
 * @method \Obsolete\Chargify\Services\SubscriptionStatus subscriptionStatus()
 */
class Chargify
{
    public function __construct(
        protected ChargifyConfig $chargifyConfig,
        protected ChargifyHttpClientInterface $chargifyHttpClient,
        protected ChargifyServiceFactoryInterface $chargifyServiceFactory
    ) {
    }

    public function getConfig(): ChargifyConfig
    {
        return $this->chargifyConfig;
    }

    public function getClient(): ChargifyHttpClientInterface
    {
        return $this->chargifyHttpClient;
    }

    public function __call($name, $arguments)
    {
        return $this->chargifyServiceFactory->getService($this, $name);
    }
}
