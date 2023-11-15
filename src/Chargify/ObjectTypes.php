<?php

namespace WinLocalInc\Chjs\Chargify;

use WinLocalInc\Chjs\Chargify\Webhook\Dunner;
use WinLocalInc\Chjs\Chargify\Webhook\Invoice;
use WinLocalInc\Chjs\Chargify\Webhook\InvoiceItem;
use WinLocalInc\Chjs\Chargify\Webhook\InvoiceNumbers;
use WinLocalInc\Chjs\Chargify\Webhook\PreviousPaymentProfile;
use WinLocalInc\Chjs\Chargify\Webhook\Site;
use WinLocalInc\Chjs\Chargify\Webhook\Statement;
use WinLocalInc\Chjs\Chargify\Webhook\Transaction;
use WinLocalInc\Chjs\Chargify\Webhook\Transactions;
use WinLocalInc\Chjs\Chargify\Webhook\UpdatedPaymentProfile;

class ObjectTypes
{
    public const TO_COLLECTION = false;

    public const mapping = [
        PaymentProfile::OBJECT_NAME => PaymentProfile::class,
        Customer::OBJECT_NAME => Customer::class,
        Subscription::OBJECT_NAME => Subscription::class,
        SubscriptionPreview::OBJECT_NAME => SubscriptionPreview::class,
        Product::OBJECT_NAME => Product::class,
        ProductFamily::OBJECT_NAME => ProductFamily::class,
        CreditCard::OBJECT_NAME => CreditCard::class,
        PublicSignUpPage::OBJECT_NAME => PublicSignUpPage::class,
        Component::OBJECT_NAME => Component::class,
        SubscriptionComponent::OBJECT_NAME => SubscriptionComponent::class,
        ComponentPrice::OBJECT_NAME => ComponentPrice::class,
        PricePoint::OBJECT_NAME => PricePoint::class,
        PricePoints::OBJECT_NAME => PricePoints::class,
        Price::OBJECT_NAME => Price::class,
        CouponCode::OBJECT_NAME => CouponCode::class,
        Allocation::OBJECT_NAME => Allocation::class,
        Site::OBJECT_NAME => Site::class,
        Invoice::OBJECT_NAME => Invoice::class,
        InvoiceItem::OBJECT_NAME => InvoiceItem::class,
        InvoiceNumbers::OBJECT_NAME => InvoiceNumbers::class,
        Statement::OBJECT_NAME => Statement::class,
        Transaction::OBJECT_NAME => Transaction::class,
        Transactions::OBJECT_NAME => Transactions::class,
        Dunner::OBJECT_NAME => Dunner::class,
        UpdatedPaymentProfile::OBJECT_NAME => UpdatedPaymentProfile::class,
        PreviousPaymentProfile::OBJECT_NAME => PreviousPaymentProfile::class,
        Migration::OBJECT_NAME => Migration::class,
        Usage::OBJECT_NAME => Usage::class,
        NextBillingManifest::OBJECT_NAME => NextBillingManifest::class,
        CurrentBillingManifest::OBJECT_NAME => CurrentBillingManifest::class,
        Preview::OBJECT_NAME => Preview::class,
        AllocationPreview::OBJECT_NAME => AllocationPreview::class,
        Coupon::OBJECT_NAME => Coupon::class,
        SubscriptionsComponents::OBJECT_NAME => SubscriptionsComponents::class,
    ];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {

                if (isset(self::mapping[$key])) {
                    $className = self::mapping[$key];
                    $this->$key = ObjectTypes::resolve($value, $className);
                } else {
                    $this->setAttribute($key, $value);
                }

            } else {
                $this->setAttribute($key, $value);
            }

        }
    }

    public static function getClassName(array $array): ?string
    {

        if (array_key_exists(0, $array) && isset(ObjectTypes::mapping[array_key_first($array[0])])) {
            return ObjectTypes::mapping[array_key_first($array[0])];
        } elseif (isset(ObjectTypes::mapping[array_key_first($array)])) {
            return ObjectTypes::mapping[array_key_first($array)];
        }

        return null;
    }

    public static function resolve(array $array, string $className)
    {
        $data = collect();
        if (isset($array[0]) && is_array($array[0])) {
            foreach ($array as $key => $values) {
                if (isset($values[$className::OBJECT_NAME])) {
                    $data->push(new $className($values[$className::OBJECT_NAME]));
                } else {
                    $data->push(new $className($values));
                }
            }

            return $data;
        }

        if (isset($array[0]) && is_array($array[0]) && isset(self::mapping[$array[0][array_key_first($array[0])]])) {
            foreach ($array as $key => $values) {
                $data->push(new $className($values[$className::OBJECT_NAME]));
            }

            return $data;
        }

        if (isset(self::mapping[array_key_first($array)]) && self::mapping[array_key_first($array)]::TO_COLLECTION) {
            foreach ($array[$className::OBJECT_NAME] as $key => $values) {
                $data->push(new $className($values));
            }

            return $data;
        }

        if (isset($array[$className::OBJECT_NAME])) {
            return new $className($array[$className::OBJECT_NAME]);
        }

        return new $className($array);
    }

    public function setAttribute($key, $value)
    {
        return $this->$key = $value;
    }
}
