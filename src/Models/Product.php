<?php

namespace WinLocalInc\Chjs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WinLocalInc\Chjs\Database\Factoriess\ProductFactory;
use WinLocalInc\Chjs\Enums\IsActive;

/**
 * @property mixed $product_handle
 */
class Product extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $primaryKey = 'product_id';

    protected $table = 'chjs_products';

    protected $guarded = [];

    protected $casts = [
        'product_is_active' => IsActive::class,
    ];

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'product_id');
    }

    protected static function newFactory()
    {
        return ProductFactory::new();
    }
}
