<?php

namespace WinLocalInc\Chjs;

use InvalidArgumentException;
use WinLocalInc\Chjs\Enums\BrokeragePricing;
use WinLocalInc\Chjs\Enums\CompanyPricing;
use WinLocalInc\Chjs\Enums\DistributorPricing;
use WinLocalInc\Chjs\Enums\FranchisePricing;
use WinLocalInc\Chjs\Enums\Product;
use WinLocalInc\Chjs\Enums\ShareCardPricing;
use WinLocalInc\Chjs\Enums\ShareCardProPricing;

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

            ];
        }

        return self::$productPricingMap;
    }

    /**
     * Checks whether a component is a main component of a product.
     *
     * @param  string  $product The product handle.
     * @param  string  $component The component handle.
     * @return bool True if it is a main component, false otherwise.
     *
     * @throws InvalidArgumentException if the product is not defined.
     */
    public static function isMainComponent(string $product, string $component): ?bool
    {
        $productPricingMap = self::getProductPricingMap();

        if (! array_key_exists($product, $productPricingMap)) {
            throw new InvalidArgumentException("Undefined product: {$product}");
        }

        if (! in_array($component, $productPricingMap[$product], true)) {
            return null;
        }

        return true;
    }
}
