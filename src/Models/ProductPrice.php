<?php

namespace WinLocalInc\Chjs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WinLocalInc\Chjs\Chjs;
use WinLocalInc\Chjs\Enums\IsActive;
use WinLocalInc\Chjs\Enums\SubscriptionInterval;

/**
 * @property SubscriptionInterval product_price_interval
 * @property mixed $product_id
 * @property Product product
 * @property mixed $product_price_handle
 */
class ProductPrice extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey   = 'product_price_id';
    protected $table        = 'chjs_product_prices';
    protected $guarded      = [];

    protected $casts = [
        'product_is_active' => IsActive::class,
        'product_price_interval' => SubscriptionInterval::class,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

}
