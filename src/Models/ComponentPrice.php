<?php

namespace WinLocalInc\Chjs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use WinLocalInc\Chjs\Chjs;
use WinLocalInc\Chjs\Enums\IsActive;

/**
 * @property mixed $component_id
 * @property mixed $product_price_id
 */
class ComponentPrice extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey   = 'component_price_id';
    protected $table        = 'chjs_component_prices';
    protected $guarded      = [];
    protected $casts = [
        'component_price_is_active' => IsActive::class,
    ];

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

}
