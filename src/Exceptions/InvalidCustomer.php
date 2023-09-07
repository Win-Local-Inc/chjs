<?php

namespace WinLocalInc\Chjs\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidCustomer extends Exception
{
    public static function notYetCreated(Model $owner): static
    {
        return new static(class_basename($owner).' is not a Chargify customer yet.');
    }
}
