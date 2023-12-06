<?php

namespace WinLocalInc\Chjs;

use WinLocalInc\Chjs\Services\ComponentPriceService;
use WinLocalInc\Chjs\Services\ComponentService;
use WinLocalInc\Chjs\Services\CoreServiceFactory;
use WinLocalInc\Chjs\Services\CouponService;
use WinLocalInc\Chjs\Services\CustomerService;
use WinLocalInc\Chjs\Services\PaymentProfileService;
use WinLocalInc\Chjs\Services\ProductPriceService;
use WinLocalInc\Chjs\Services\ProductService;
use WinLocalInc\Chjs\Services\SubscriptionComponentService;
use WinLocalInc\Chjs\Services\SubscriptionService;
use WinLocalInc\Chjs\Services\SubscriptionStatusService;
use WinLocalInc\Chjs\Services\CustomFieldsService;

/**
 * Service factory class for API resources in the root namespace.
 *
 * @property CustomerService $customer
 * @property CouponService $coupon
 * @property SubscriptionService $subscription
 * @property PaymentProfileService $paymentProfile
 * @property ProductPriceService $productPrice
 * @property SubscriptionComponentService $subscriptionComponent
 * @property ProductService $product
 * @property ComponentService $component
 * @property ComponentPriceService $componentPrice
 * @property SubscriptionStatusService $subscriptionStatus
 * @property CustomFieldsService $customFields
 **/
class Chjs extends ChjsBase
{
    private ?CoreServiceFactory $coreServiceFactory = null;

    public function __get($name)
    {
        return $this->getService($name);
    }

    public function getService($name)
    {
        if ($this->coreServiceFactory === null) {
            $this->coreServiceFactory = new CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->getService($name);
    }
}
