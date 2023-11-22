<?php

namespace WinLocalInc\Chjs\Services;

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
 *
 **/
class CoreServiceFactory extends AbstractServiceFactory
{
    private static array $classMap = [
        'subscription' => SubscriptionService::class,
        'customer' => CustomerService::class,
        'coupon' => CouponService::class,
        'paymentProfile' => PaymentProfileService::class,
        'productPrice' => ProductPriceService::class,
        'subscriptionComponent' => SubscriptionComponentService::class,
        'product' => ProductService::class,
        'component' => ComponentService::class,
        'componentPrice' => ComponentPriceService::class,
        'subscriptionStatus' => SubscriptionStatusService::class,
        'customFields' => CustomFieldsService::class,
    ];

    protected function getServiceClass(string $name): ?string
    {
        return array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
