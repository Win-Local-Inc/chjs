<?php

namespace WinLocalInc\Chjs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use WinLocalInc\Chjs\Database\Factories\ComponentFactory;
use WinLocalInc\Chjs\Enums\IsActive;
use WinLocalInc\Chjs\Enums\MainComponent;

/**
 * @property mixed $component_id
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property ComponentPrice price
 */
class Component extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $primaryKey = 'component_id';

    protected $table = 'chjs_components';

    protected $guarded = [];

    protected $casts = [
        'component_is_active' => IsActive::class,
    ];

    public function price(): HasOne
    {
        return $this->hasOne(ComponentPrice::class, 'component_id');
    }

    protected static function newFactory(): ComponentFactory
    {
        return ComponentFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            try{
                $model->component_entry = MainComponent::findComponent($model->compoent_handle)->name;
            }
            catch (\Exception $e){}
        });
    }
}
