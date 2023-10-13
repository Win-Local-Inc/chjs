<?php

namespace WinLocalInc\Chjs\Enums;

trait EnumHelpers
{
    public static function keyValue()
    {
        $result = [];

        foreach (self::cases() as $index => $case) {
            $result[$case->value] = $case->name;
        }

        return $result;
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }

    public function is(self $case): bool
    {
        return $this === $case;
    }
}
