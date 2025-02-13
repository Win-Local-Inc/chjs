<?php

namespace WinLocalInc\Chjs;

use WinLocalInc\Chjs\Enums\BrokeragePricing;
use WinLocalInc\Chjs\Enums\CompanyPricing;
use WinLocalInc\Chjs\Enums\DistributorPricing;
use WinLocalInc\Chjs\Enums\FranchisePricing;
use WinLocalInc\Chjs\Enums\Product;
use WinLocalInc\Chjs\Enums\ShareCardPricing;
use WinLocalInc\Chjs\Enums\ShareCardProPricing;
use WinLocalInc\Chjs\Models\Subscription;

class ProductStructure
{
    private static array $productPricingMap = [];

    /**
     * Gets the pricing map for the product.
     *
     * @return array The product pricing map.
     */
    public static function getProductPricingMap(): array
    {
        if (empty(self::$productPricingMap)) {
            self::$productPricingMap = [
                Product::ENTREPRENEUR->value => array_merge(
                    CompanyPricing::values(),
                    BrokeragePricing::values()
                ),
                Product::FRANCHISER->value => FranchisePricing::values(),
                Product::DISTRIBUTOR->value => DistributorPricing::values(),
                Product::PROMO->value => ShareCardPricing::values(),
                Product::SOLO->value => array_merge(
                    ShareCardProPricing::values(),
                    ShareCardPricing::values()
                ),
                Product::PKG_PART_TIME->value => [
                    CompanyPricing::ZERO->value,
                    ShareCardProPricing::ZERO->value,
                ],
                Product::PKG_FULL_TIME->value => [
                    CompanyPricing::ZERO->value,
                    ShareCardProPricing::ZERO->value,
                ],
                Product::PKG_OVERTIME->value => [
                    CompanyPricing::ZERO->value,
                ],
                Product::MONTHLY_CONTENT_MANAGEMENT->value => array_merge(
                    ShareCardProPricing::values(),
                    ShareCardPricing::values()
                ),
            ];
        }

        return self::$productPricingMap;
    }

    public static function setMainComponent(Subscription $subscription): void
    {
        $subscriptionComponents = $subscription->subscriptionComponents;
        $product = $subscription->product_handle->value;

        $subscriptionComponents->each(function ($component) {
            $component->update(['is_main_component' => null]);
        });

        $productPricingMap = self::getProductPricingMap();

        foreach ($subscriptionComponents as $component) {
            $componentHandle = $component->component_handle;
            if (isset($productPricingMap[$product]) && in_array($componentHandle, $productPricingMap[$product])) {

                if (in_array($product, [Product::PKG_PART_TIME->value, Product::PKG_FULL_TIME->value])) {
                    if ($subscriptionComponents->contains('component_handle', CompanyPricing::ZERO->value) && $componentHandle != CompanyPricing::ZERO->value) {
                        continue;
                    }
                }
                if ($component->subscription_component_quantity > 0) {
                    $component->update(['is_main_component' => true]);
                    break;
                }
            }
        }
    }
}
