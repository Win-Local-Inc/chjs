<?php

namespace WinLocalInc\Chjs\Enums;

enum ProductPricing: string
{
    use EnumHelpers;
    case SOLO_MONTH = 'solo_month';
    case SOLO_BIANNUAL = 'solo_biannual';
    case SOLO_YEAR = 'solo_year';
    case PROMO_MONTH = 'promo_month';
    case PROMO_BIANNUAL = 'promo_biannual';
    case PROMO_YEAR = 'promo_year';
    case ENTREPRENEUR_MONTH = 'entrepreneur_month';
    case ENTREPRENEUR_BIANNUAL = 'entrepreneur_biannual';
    case ENTREPRENEUR_YEAR = 'entrepreneur_year';
    case FRANCHISER_MONTH = 'franchiser_month';
    case FRANCHISER_BIANNUAL = 'franchiser_biannual';
    case FRANCHISER_YEAR = 'franchiser_year';
    case DISTRIBUTOR_MONTH = 'distributor_month';
    case DISTRIBUTOR_BIANNUAL = 'distributor_biannual';
    case DISTRIBUTOR_YEAR = 'distributor_year';
    case PKG_PART_TIME_MONTH = 'pkg_part_time_month';
    case PKG_PART_TIME_BIANNUAL = 'pkg_part_time_biannual';
    case PKG_FULL_TIME_MONTH = 'pkg_full_time_month';
    case PKG_FULL_TIME_BIANNUAL = 'pkg_full_time_biannual';
    case PKG_OVERTIME_MONTH = 'pkg_overtime_month';
    case PKG_OVERTIME_BIANNUAL = 'pkg_overtime_biannual';
    //
    //    case default  = 'custom_product';

    public static function getProductPrices(Product $product)
    {
        $prices = [];
        foreach (self::cases() as $pricing) {
            if (str_starts_with($pricing->value, $product->value)) {
                $prices[] = $pricing->value;
            }
        }

        return $prices;
    }

    public static function productPricesMapping(): array
    {
        $allPrices = [];
        foreach (Product::cases() as $product) {
            $allPrices[$product->value] = self::getProductPrices($product);
        }

        return $allPrices;
    }
}
