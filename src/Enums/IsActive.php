<?php

namespace WinLocalInc\Chjs\Enums;

enum IsActive: int
{
    use EnumHelpers;
    case Active = 1;
    case Inactive = 0;
}
