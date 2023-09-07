<?php

namespace WinLocalInc\Chjs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WinLocalInc\Chjs\Chjs;
use WinLocalInc\Chjs\Enums\IsActive;

/**
 * @property mixed $component_id
 */
class Component extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey   = 'component_id';
    protected $table        = 'chjs_components';
    protected $guarded      = [];
    protected $casts = [
        'component_is_active' => IsActive::class,
    ];

    public function componentPricePoints(): HasMany
    {
        return $this->hasMany(ComponentPrice::class, 'component_id');
    }
}
