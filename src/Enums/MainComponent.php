<?php

namespace WinLocalInc\Chjs\Enums;

use Exception;

enum MainComponent
{
    use EnumHelpers;
    case SHARE_CARD; // ShareCardPricing::class;
    case SHARE_CARD_PRO; // ShareCardProPricing::class;
    case BROKERAGE; // BrokeragePricing::class;
    case COMPANY; // CompanyPricing::class;
    case FRANCHISE; // FranchisePricing::class;
    case DISTRIBUTOR; // DistributorPricing::class;

    /**
     * @throws Exception
     */
    public static function findComponent(string $componentHandle): self
    {
        foreach (MainComponent::cases() as $mainComponent) {
            $pricingClass = self::convertEnumToClassName($mainComponent->name);
            if (in_array($componentHandle, $pricingClass::values(), true)) {
                return $mainComponent;
            }
        }

        throw new Exception("Component handler: {$componentHandle} doesn't exists");
    }

    private static function convertEnumToClassName(string $enumValue): string
    {
        $words = explode('_', strtolower($enumValue));
        $words[] = 'pricing';
        $camelCase = array_map('ucfirst', $words);
        $class = implode('', $camelCase);

        return 'WinLocalInc\\Chjs\\Enums\\'.$class;
    }

    public static function componentNames(): array
    {
        $names = [];
        foreach (MainComponent::cases() as $mainComponent) {
            $names[$mainComponent->name] = ucwords(strtolower(str_replace('_', ' ', $mainComponent->name)));
        }

        return $names;
    }
}
