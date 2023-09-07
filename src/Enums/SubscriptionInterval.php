<?php

namespace WinLocalInc\Chjs\Enums;

enum SubscriptionInterval: string
{
    case Month = 'month';
    case Year = 'year';

    public static function getIntervalUnit(int $interval): SubscriptionInterval
    {
        if($interval === 1)
        {
            return SubscriptionInterval::Month;
        }

        return SubscriptionInterval::Year;
    }

    public function getInterval(): int
    {
        if($this == SubscriptionInterval::Month)
        {
            return 1;
        }

        return 12;
    }
}
