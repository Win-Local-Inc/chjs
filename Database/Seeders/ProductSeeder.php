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
                "product_id" => 6597782,
                "product_handle" => "solo",
                "product_name" => "Solo",
                "product_is_active" => 1,
                "children_self_payment" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-18 16:29:21"
            ],
            [
                "product_id" => 6597783,
                "product_handle" => "promo",
                "product_name" => "Promo",
                "product_is_active" => 1,
                "children_self_payment" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-18 13:35:25"
            ],
            [
                "product_id" => 6597784,
                "product_handle" => "entrepreneur",
                "product_name" => "Entrepreneur",
                "product_is_active" => 1,
                "children_self_payment" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-20 13:39:45"
            ],
            [
                "product_id" => 6597785,
                "product_handle" => "franchiser",
                "product_name" => "Franchiser ",
                "product_is_active" => 1,
                "children_self_payment" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-18 16:28:06"
            ],
            [
                "product_id" => 6597786,
                "product_handle" => "distributor",
                "product_name" => "Distributor",
                "product_is_active" => 1,
                "children_self_payment" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-18 13:35:56"
            ],
            [
                "product_id" => 6597787,
                "product_handle" => "pkg_part_time",
                "product_name" => "Part-Time Engagement",
                "product_is_active" => 1,
                "children_self_payment" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-20 07:35:15"
            ],
            [
                "product_id" => 6597788,
                "product_handle" => "pkg_full_time",
                "product_name" => "Full-Time Engagement",
                "product_is_active" => 1,
                "children_self_payment" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-20 16:18:06"
            ],
            [
                "product_id" => 6597789,
                "product_handle" => "pkg_overtime",
                "product_name" => "Overtime Engagement",
                "product_is_active" => 1,
                "children_self_payment" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_id" => 6606381,
                "product_handle" => "once_off",
                "product_name" => "Once-off",
                "product_is_active" => 1,
                "children_self_payment" => 1,
                "created_at" => "2023-12-28 11:47:29",
                "updated_at" => "2023-12-28 11:47:50"
            ]
        ];

    }

    private function productPriceDummyData(): array
    {
        return [
            [
                "product_price_id" => 2570545,
                "product_id" => 6597782,
                "product_handle" => "solo",
                "product_price_handle" => "solo_biannual",
                "product_price_name" => "Solo biannual",
                "product_price_interval" => "biannual",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-13 09:32:24"
            ],
            [
                "product_price_id" => 2570546,
                "product_id" => 6597782,
                "product_handle" => "solo",
                "product_price_handle" => "solo_month",
                "product_price_name" => "Solo month",
                "product_price_interval" => "month",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-13 08:30:53"
            ],
            [
                "product_price_id" => 2570547,
                "product_id" => 6597782,
                "product_handle" => "solo",
                "product_price_handle" => "solo_year",
                "product_price_name" => "Solo year",
                "product_price_interval" => "year",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-13 08:30:53"
            ],
            [
                "product_price_id" => 2570548,
                "product_id" => 6597783,
                "product_handle" => "promo",
                "product_price_handle" => "promo_biannual",
                "product_price_name" => "Promo biannual",
                "product_price_interval" => "biannual",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-13 08:30:53"
            ],
            [
                "product_price_id" => 2570549,
                "product_id" => 6597783,
                "product_handle" => "promo",
                "product_price_handle" => "promo_month",
                "product_price_name" => "Promo month",
                "product_price_interval" => "month",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-13 08:30:53"
            ],
            [
                "product_price_id" => 2570550,
                "product_id" => 6597783,
                "product_handle" => "promo",
                "product_price_handle" => "promo_year",
                "product_price_name" => "Promo year",
                "product_price_interval" => "year",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-13 08:30:53"
            ],
            [
                "product_price_id" => 2570551,
                "product_id" => 6597784,
                "product_handle" => "entrepreneur",
                "product_price_handle" => "entrepreneur_biannual",
                "product_price_name" => "Entrepreneur biannual",
                "product_price_interval" => "biannual",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-13 08:30:53"
            ],
            [
                "product_price_id" => 2570552,
                "product_id" => 6597784,
                "product_handle" => "entrepreneur",
                "product_price_handle" => "entrepreneur_month",
                "product_price_name" => "Entrepreneur month",
                "product_price_interval" => "month",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-13 08:30:53"
            ],
            [
                "product_price_id" => 2570553,
                "product_id" => 6597784,
                "product_handle" => "entrepreneur",
                "product_price_handle" => "entrepreneur_year",
                "product_price_name" => "Entrepreneur year",
                "product_price_interval" => "year",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:53",
                "updated_at" => "2023-12-13 08:30:53"
            ],
            [
                "product_price_id" => 2570554,
                "product_id" => 6597785,
                "product_handle" => "franchiser",
                "product_price_handle" => "franchiser_biannual",
                "product_price_name" => "Franchiser biannual",
                "product_price_interval" => "biannual",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570555,
                "product_id" => 6597785,
                "product_handle" => "franchiser",
                "product_price_handle" => "franchiser_month",
                "product_price_name" => "Franchiser month",
                "product_price_interval" => "month",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570556,
                "product_id" => 6597785,
                "product_handle" => "franchiser",
                "product_price_handle" => "franchiser_year",
                "product_price_name" => "Franchiser year",
                "product_price_interval" => "year",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570557,
                "product_id" => 6597786,
                "product_handle" => "distributor",
                "product_price_handle" => "distributor_biannual",
                "product_price_name" => "Distributor biannual",
                "product_price_interval" => "biannual",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570558,
                "product_id" => 6597786,
                "product_handle" => "distributor",
                "product_price_handle" => "distributor_month",
                "product_price_name" => "Distributor month",
                "product_price_interval" => "month",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570559,
                "product_id" => 6597786,
                "product_handle" => "distributor",
                "product_price_handle" => "distributor_year",
                "product_price_name" => "Distributor year",
                "product_price_interval" => "year",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570560,
                "product_id" => 6597787,
                "product_handle" => "pkg_part_time",
                "product_price_handle" => "pkg_part_time_biannual",
                "product_price_name" => "Part-Time Engagement biannual",
                "product_price_interval" => "biannual",
                "product_price_in_cents" => 48000,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570561,
                "product_id" => 6597787,
                "product_handle" => "pkg_part_time",
                "product_price_handle" => "pkg_part_time_month",
                "product_price_name" => "Part-Time Engagement month",
                "product_price_interval" => "month",
                "product_price_in_cents" => 10000,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570562,
                "product_id" => 6597788,
                "product_handle" => "pkg_full_time",
                "product_price_handle" => "pkg_full_time_biannual",
                "product_price_name" => "Full-Time Engagement biannual",
                "product_price_interval" => "biannual",
                "product_price_in_cents" => 72000,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570563,
                "product_id" => 6597788,
                "product_handle" => "pkg_full_time",
                "product_price_handle" => "pkg_full_time_month",
                "product_price_name" => "Full-Time Engagement month",
                "product_price_interval" => "month",
                "product_price_in_cents" => 15000,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570564,
                "product_id" => 6597789,
                "product_handle" => "pkg_overtime",
                "product_price_handle" => "pkg_overtime_biannual",
                "product_price_name" => "Overtime Engagement biannual",
                "product_price_interval" => "biannual",
                "product_price_in_cents" => 240000,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2570565,
                "product_id" => 6597789,
                "product_handle" => "pkg_overtime",
                "product_price_handle" => "pkg_overtime_month",
                "product_price_name" => "Overtime Engagement month",
                "product_price_interval" => "month",
                "product_price_in_cents" => 50000,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-13 08:30:54",
                "updated_at" => "2023-12-13 08:30:54"
            ],
            [
                "product_price_id" => 2594440,
                "product_id" => 6606381,
                "product_handle" => "once_off",
                "product_price_handle" => "once_off",
                "product_price_name" => "Once-off",
                "product_price_interval" => "month",
                "product_price_in_cents" => 0,
                "product_price_is_active" => 1,
                "created_at" => "2023-12-28 11:47:29",
                "updated_at" => "2023-12-28 11:47:50"
            ]
        ];

    }
}
