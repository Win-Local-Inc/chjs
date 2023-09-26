<?php

namespace WinLocalInc\Chjs;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Chjs\Skeleton\SkeletonClass
 */
class ChjsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Chjs::class;
    }
}
