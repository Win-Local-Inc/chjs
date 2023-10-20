<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;
use WinLocalInc\Chjs\Chargify\ChargifyObject;

class CouponService extends AbstractService
{
    public function create(
        string $productFamilyId,
        array $coupon,
        array $restrictedProducts = null,
        array $restrictedComponents = null
    ): ChargifyObject {
        $this->validatePayload($coupon, [
            'name' => 'required|string',
            'description' => 'required|string',
            'code' => ['required', 'regex:/^[A-Z0-9%@+\-_.]+$/'],
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

        return $this->post('product_families/'.$productFamilyId.'/coupons', $parameters);
    }

    public function update(
        string $productFamilyId,
        string $couponId,
        array $coupon,
        array $restrictedProducts = null,
        array $restrictedComponents = null
    ): ChargifyObject {
        $this->validatePayload($coupon, [
            'code' => ['sometimes', 'regex:/^[A-Z0-9%@+\-_.]+$/'],
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

        return $this->put('product_families/'.$productFamilyId.'/coupons/'.$couponId, $parameters);
    }

    public function archive(string $productFamilyId, string $couponId): ChargifyObject
    {
        return $this->delete('product_families/'.$productFamilyId.'/coupons/'.$couponId);
    }

    public function getCouponById(string $productFamilyId, string $couponId): ChargifyObject
    {
        return $this->get('product_families/'.$productFamilyId.'/coupons/'.$couponId);
    }

    public function getCouponByCode(string $productFamilyId, string $code): ChargifyObject
    {
        return $this->get('coupons/find', [
            'product_family_id' => $productFamilyId,
            'code' => $code,
        ]);
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

        return $this->get('coupons', $parameters);
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

        return $this->get('product_families/'.$productFamilyId.'/coupons', $parameters);
    }

    public function listCouponUsages(string $productFamilyId, string $couponId): Collection
    {
        return collect($this->get('product_families/'.$productFamilyId.'/coupons/'.$couponId.'/usage', [], true));
    }

    public function validateCode(string $productFamilyId, string $code): ChargifyObject
    {
        return $this->get('coupons/validate', [
            'product_family_id' => $productFamilyId,
            'code' => $code,
        ]);
    }

    public function createUpdateCouponSubcodes(string $couponId, array $subcodes): array
    {
        return $this->post('coupons/'.$couponId.'/codes', ['codes' => $subcodes], true);
    }

    public function delteCouponSubcode(string $couponId, string $subcode): void
    {
        $this->delete('coupons/'.$couponId.'/codes/'.$subcode, [], true);
    }

    public function listCouponSubcodes(string $couponId, array $parameters = []): array
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return collect($this->get('coupons/'.$couponId.'/codes', $parameters, true))->flatten()->toArray();
    }
}
