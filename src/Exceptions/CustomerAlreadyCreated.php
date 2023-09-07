<?php

namespace WinLocalInc\Chjs\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class CustomerAlreadyCreated extends Exception
{
    public static function exists(Model $owner) :static
    {
        return new static(class_basename($owner)." is already a Chargify customer with ID {$owner->chargify_id}.");
    }
}
