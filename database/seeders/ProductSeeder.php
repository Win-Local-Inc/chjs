<?php

namespace WinLocalInc\Chjs\Database\Seeders;

use Illuminate\Database\Seeder;
use WinLocalInc\Chjs\Models\Product;
use WinLocalInc\Chjs\Models\ProductPrice;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert($this->productDummyData());
        ProductPrice::insert($this->productPriceDummyData());
    }

    private function productDummyData(): array
    {
        return [
            [
                'product_id' => 6569418,
                'product_handle' => 'solo',
                'product_name' => 'Solo',
                'product_is_active' => 1,
                'children_self_payment' => 1,
            ],
            [
                'product_id' => 6570189,
                'product_handle' => 'promo',
                'product_name' => 'Promo',
                'product_is_active' => 1,
                'children_self_payment' => 1,
            ],
            [
                'product_id' => 6570190,
                'product_handle' => 'entrepreneur',
                'product_name' => 'Entrepreneur',
                'product_is_active' => 1,
                'children_self_payment' => 1,
            ],
            [
                'product_id' => 6570191,
                'product_handle' => 'franchiser',
                'product_name' => 'Franchiser ',
                'product_is_active' => 1,
                'children_self_payment' => 1,
            ],
            [
                'product_id' => 6570192,
                'product_handle' => 'distributor',
                'product_name' => 'Distributor',
                'product_is_active' => 1,
                'children_self_payment' => 1,
            ],
            [
                'product_id' => 6582799,
                'product_handle' => 'pkg_part_time',
                'product_name' => 'Part-Time Engagement',
                'product_is_active' => 1,
                'children_self_payment' => 1,
            ],
            [
                'product_id' => 6582800,
                'product_handle' => 'pkg_full_time',
                'product_name' => 'Full-Time Engagement',
                'product_is_active' => 1,
                'children_self_payment' => 1,
            ],
            [
                'product_id' => 6582801,
                'product_handle' => 'pkg_overtime',
                'product_name' => 'Overtime Engagement',
                'product_is_active' => 1,
                'children_self_payment' => 1,
            ],
        ];

    }

    private function productPriceDummyData(): array
    {
        return [
            [
                'product_price_id' => 2497857,
                'product_id' => 6569418,
                'product_handle' => 'solo',
                'product_price_handle' => 'solo_month',
                'product_price_name' => 'Solo month',
                'product_price_interval' => 'month',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499570,
                'product_id' => 6569418,
                'product_handle' => 'solo',
                'product_price_handle' => 'solo_biannual',
                'product_price_name' => 'Solo biannual',
                'product_price_interval' => 'biannual',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499571,
                'product_id' => 6569418,
                'product_handle' => 'solo',
                'product_price_handle' => 'solo_year',
                'product_price_name' => 'Solo year',
                'product_price_interval' => 'year',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499576,
                'product_id' => 6570189,
                'product_handle' => 'promo',
                'product_price_handle' => 'promo_month',
                'product_price_name' => 'Promo month',
                'product_price_interval' => 'month',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499577,
                'product_id' => 6570189,
                'product_handle' => 'promo',
                'product_price_handle' => 'promo_biannual',
                'product_price_name' => 'Promo biannual',
                'product_price_interval' => 'biannual',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499578,
                'product_id' => 6570189,
                'product_handle' => 'promo',
                'product_price_handle' => 'promo_year',
                'product_price_name' => 'Promo year',
                'product_price_interval' => 'year',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499579,
                'product_id' => 6570190,
                'product_handle' => 'entrepreneur',
                'product_price_handle' => 'entrepreneur_month',
                'product_price_name' => 'Entrepreneur month',
                'product_price_interval' => 'month',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499580,
                'product_id' => 6570190,
                'product_handle' => 'entrepreneur',
                'product_price_handle' => 'entrepreneur_biannual',
                'product_price_name' => 'Entrepreneur biannual',
                'product_price_interval' => 'biannual',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499581,
                'product_id' => 6570190,
                'product_handle' => 'entrepreneur',
                'product_price_handle' => 'entrepreneur_year',
                'product_price_name' => 'Entrepreneur year',
                'product_price_interval' => 'year',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499582,
                'product_id' => 6570191,
                'product_handle' => 'franchiser',
                'product_price_handle' => 'franchiser_month',
                'product_price_name' => 'Franchiser month',
                'product_price_interval' => 'month',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499584,
                'product_id' => 6570191,
                'product_handle' => 'franchiser',
                'product_price_handle' => 'franchiser_biannual',
                'product_price_name' => 'Franchiser biannual',
                'product_price_interval' => 'biannual',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499585,
                'product_id' => 6570191,
                'product_handle' => 'franchiser',
                'product_price_handle' => 'franchiser_year',
                'product_price_name' => 'Franchiser year',
                'product_price_interval' => 'year',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499586,
                'product_id' => 6570192,
                'product_handle' => 'distributor',
                'product_price_handle' => 'distributor_month',
                'product_price_name' => 'Distributor month',
                'product_price_interval' => 'month',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499588,
                'product_id' => 6570192,
                'product_handle' => 'distributor',
                'product_price_handle' => 'distributor_biannual',
                'product_price_name' => 'Distributor biannual',
                'product_price_interval' => 'biannual',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2499589,
                'product_id' => 6570192,
                'product_handle' => 'distributor',
                'product_price_handle' => 'distributor_year',
                'product_price_name' => 'Distributor year',
                'product_price_interval' => 'year',
                'product_price_in_cents' => 0,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2531764,
                'product_id' => 6582799,
                'product_handle' => 'pkg_part_time',
                'product_price_handle' => 'pkg_part_time_month',
                'product_price_name' => 'Part-Time Engagement month',
                'product_price_interval' => 'month',
                'product_price_in_cents' => 10000,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2531765,
                'product_id' => 6582799,
                'product_handle' => 'pkg_part_time',
                'product_price_handle' => 'pkg_part_time_biannual',
                'product_price_name' => 'Part-Time Engagement biannual',
                'product_price_interval' => 'biannual',
                'product_price_in_cents' => 48000,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2531766,
                'product_id' => 6582800,
                'product_handle' => 'pkg_full_time',
                'product_price_handle' => 'pkg_full_time_month',
                'product_price_name' => 'Full-Time Engagement month',
                'product_price_interval' => 'month',
                'product_price_in_cents' => 15000,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2531767,
                'product_id' => 6582800,
                'product_handle' => 'pkg_full_time',
                'product_price_handle' => 'pkg_full_time_biannual',
                'product_price_name' => 'Full-Time Engagement biannual',
                'product_price_interval' => 'biannual',
                'product_price_in_cents' => 72000,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2531768,
                'product_id' => 6582801,
                'product_handle' => 'pkg_overtime',
                'product_price_handle' => 'pkg_overtime_month',
                'product_price_name' => 'Overtime Engagement month',
                'product_price_interval' => 'month',
                'product_price_in_cents' => 50000,
                'product_price_is_active' => 1,
            ],
            [
                'product_price_id' => 2531770,
                'product_id' => 6582801,
                'product_handle' => 'pkg_overtime',
                'product_price_handle' => 'pkg_overtime_biannual',
                'product_price_name' => 'Overtime Engagement biannual',
                'product_price_interval' => 'biannual',
                'product_price_in_cents' => 240000,
                'product_price_is_active' => 1,
            ],
        ];

    }
}
