<?php

namespace WinLocalInc\Chjs;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use WinLocalInc\Chjs\Models\Component;
use WinLocalInc\Chjs\Models\ComponentPrice;
use WinLocalInc\Chjs\Models\Coupon;
use WinLocalInc\Chjs\Models\Product;
use WinLocalInc\Chjs\Models\ProductPrice;
use WinLocalInc\Chjs\Models\Subscription;
use WinLocalInc\Chjs\Models\SubscriptionComponent;


class ChjsBase
{
    public static string $userModel = 'App\\Models\\User';
    public static string $subscriberModel = 'App\\Models\\Workspace\\Workspace';
    public static string $subscriptionModel = Subscription::class;
    public static string $subscriptionComponentModel = SubscriptionComponent::class;
    public static string $componentModel = Component::class;
    public static string $componentPriceModel = ComponentPrice::class;
    public static string $productModel = Product::class;
    public static string $productPriceModel = ProductPrice::class;
    public static string $couponModel = Coupon::class;


    protected PendingRequest $httpClient;
    public function __construct(protected string $hostname, protected string $apiKey, protected int $timeout = 150)
    {
        $this->httpClient = Http::acceptJson()
            ->asJson()
            ->baseUrl($this->hostname)
            ->timeout($this->timeout)
            ->withBasicAuth($this->apiKey, 'x');
    }

    public function getClient(): PendingRequest
    {
        return $this->httpClient;
    }

    public static function useUserModel(string $userModel):void
    {
        static::$userModel = $userModel;
    }

    public static function useSubscriptionModel(string $subscriptionModel):void
    {
        static::$subscriptionModel = $subscriptionModel;
    }

    public static function useSubscriptionComponentModel(string $subscriptionComponentModel):void
    {
        static::$subscriptionComponentModel = $subscriptionComponentModel;
    }

    public static function useComponentModel(string $componentModel):void
    {
        static::$componentModel = $componentModel;
    }

    public static function useComponentPriceModel(string $componentPriceModel):void
    {
        static::$componentPriceModel = $componentPriceModel;
    }

    public static function useProductModel(string $productModel):void
    {
        static::$productModel = $productModel;
    }

    public static function useSubscriberModel(string $subscriberModel):void
    {
        static::$subscriberModel = $subscriberModel;
    }

    public static function useProductPriceModel(string $productPriceModel):void
    {
        static::$productPriceModel = $productPriceModel;
    }

    public static function useCouponModel(string $couponModel):void
    {
        static::$couponModel = $couponModel;
    }
}
