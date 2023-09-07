<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class Coupon extends AbstractService
{
    public function create(
        string $productFamilyId,
        array $coupon,
        array $restrictedProducts = null,
        array $restrictedComponents = null
    ): array {
        $this->validatePayload($coupon, [
            'name' => 'required|string',
            'description' => 'required|string',
            'code' => ['required', 'regex:/^[A-Z%@+-_.]+$/'],
            'percentage' => 'required_without:amount_in_cents|prohibits:amount_in_cents|numeric',
            'amount_in_cents' => 'required_without:percentage|prohibits:percentage|integer|min:1',
        ]);

        $parameters = [
            'coupon' => $coupon,
        ];

        if ($restrictedProducts) {
            $parameters['restricted_products'] = $restrictedProducts;
        }
        if ($restrictedComponents) {
            $parameters['restricted_components'] = $restrictedComponents;
        }

        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/coupons', 'post', $parameters)
            ->json('coupon', []);
    }

    public function update(
        string $productFamilyId,
        string $couponId,
        array $coupon,
        array $restrictedProducts = null,
        array $restrictedComponents = null
    ): array {
        $this->validatePayload($coupon, [
            'code' => ['sometimes', 'regex:/^[A-Z%@+-_.]+$/'],
        ]);

        $parameters = [
            'coupon' => $coupon,
        ];

        if ($restrictedProducts) {
            $parameters['restricted_products'] = $restrictedProducts;
        }
        if ($restrictedComponents) {
            $parameters['restricted_components'] = $restrictedComponents;
        }

        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/coupons/'.$couponId, 'put', $parameters)
            ->json('coupon', []);
    }

    public function archive(string $productFamilyId, string $couponId): array
    {
        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/coupons/'.$couponId, 'delete')
            ->json('coupon', []);
    }

    public function getCouponById(string $productFamilyId, string $couponId): array
    {
        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/coupons/'.$couponId, 'get')
            ->json('coupon', []);
    }

    public function getCouponByCode(string $productFamilyId, string $code): array
    {
        return $this->getClient()
            ->request('coupons/find', 'get', [
                'product_family_id' => $productFamilyId,
                'code' => $code,
            ])
            ->json('coupon', []);
    }

    public function listCoupons(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'filter' => 'sometimes|array',
            'filter.codes' => 'sometimes|string',
            'filter.ids' => 'sometimes|string',
            'filter.date_field' => 'sometimes|string|in:updated_at,created_at',
            'filter.start_date' => 'sometimes|date_format:Y-m-d',
            'filter.end_date' => 'sometimes|date_format:Y-m-d',
            'filter.start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'filter.end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        return $this->getClient()
            ->request('coupons', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['coupon']);

    }

    public function listCouponsForProductFamily(string $productFamilyId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'filter' => 'sometimes|array',
            'filter.codes' => 'sometimes|string',
            'filter.ids' => 'sometimes|string',
            'filter.date_field' => 'sometimes|string|in:updated_at,created_at',
            'filter.start_date' => 'sometimes|date_format:Y-m-d',
            'filter.end_date' => 'sometimes|date_format:Y-m-d',
            'filter.start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'filter.end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/coupons.json', 'get', $parameters)
            ->collect()
            ->map(fn ($item) => $item['coupon']);

    }

    public function listCouponUsages(string $productFamilyId, string $couponId): Collection
    {
        return $this->getClient()
            ->request('product_families/'.$productFamilyId.'/coupons/'.$couponId.'/usage', 'get')
            ->collect();
    }

    public function validateCode(string $productFamilyId, string $code): array
    {
        return $this->getClient()
            ->request('coupons/validate', 'get', [
                'product_family_id' => $productFamilyId,
                'code' => $code,
            ])
            ->json('coupon', []);
    }

    public function createUpdateCouponSubcodes(string $couponId, array $subcodes): Collection
    {
        return $this->getClient()
            ->request('coupons/'.$couponId.'/codes', 'post', ['codes' => $subcodes])
            ->collect();
    }

    public function delteCouponSubcode(string $couponId, string $subcode): void
    {
        $this->getClient()
            ->request('coupons/'.$couponId.'/codes/'.$subcode, 'delete');
    }

    public function listCouponSubcodes(string $couponId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->getClient()
            ->request('coupons/'.$couponId.'/codes', 'get', $parameters)
            ->collect()
            ->flatten();
    }
}
