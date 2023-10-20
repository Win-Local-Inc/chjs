<?php

namespace WinLocalInc\Chjs\Enums;

enum MainComponent: string
{
    use EnumHelpers;
    case SHARECARD = ShareCardPricing::class;
    case SHARECARD_PRO = ShareCardProPricing::class;
    case BROKERAGE = BrokeragePricing::class;
    case COMPANY = CompanyPricing::class;
    case FRANCHISE = FranchisePricing::class;
    case DISTRIBUTOR = DistributorPricing::class;

    public static function findName(string $componentHandle): ?string
    {
        foreach (MainComponent::cases() as $mainComponent) {
            $nestedEnumClass = $mainComponent->value;
            if (in_array($componentHandle, $nestedEnumClass::values(), true)) {
                return $mainComponent->name;
            }
        }

        return null;
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
