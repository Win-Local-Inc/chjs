<?php

namespace WinLocalInc\Chjs\Models;

use App\Models\Workspace\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WinLocalInc\Chjs\Chjs;
use WinLocalInc\Chjs\Database\Factoriess\SubscriptionFactory;
use WinLocalInc\Chjs\Enums\MainComponent;
use WinLocalInc\Chjs\Enums\PaymentCollectionMethod;
use WinLocalInc\Chjs\Enums\ProductPricing;
use WinLocalInc\Chjs\Enums\SubscriptionInterval;
use WinLocalInc\Chjs\Enums\SubscriptionStatus;

/**
 * @property Workspace workspace
 * @property Product product
 * @property mixed $subscription_id
 * @property mixed $product_handle
 * @property mixed $component
 * @property mixed subscriptionComponents
 * @property ProductPricing product_price_handle
 */
class Subscription extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $primaryKey = 'workspace_id';

    protected $table = 'chjs_subscriptions';

    protected $guarded = [];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'product_handle' => \WinLocalInc\Chjs\Enums\Product::class,
        'product_price_handle' => ProductPricing::class,
        'payment_collection_method' => PaymentCollectionMethod::class,
        'subscription_interval' => SubscriptionInterval::class,
        'component' => MainComponent::class,
    ];

    public function getProductNameAttribute()
    {
        return $this->product_handle->value.'::'.$this->component->name;
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Chjs::$subscriberModel, 'workspace_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Chjs::$userModel, 'user_id');
    }

    public function productPrice(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class, 'product_price_handle');
    }

    public function subscriptionComponents(): HasMany
    {
        return $this->hasMany(SubscriptionComponent::class, 'subscription_id', 'subscription_id');
    }

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionComponent::class, Subscription::class, 'subscription_id', 'subscription_id');
    }

    protected static function newFactory()
    {
        return SubscriptionFactory::new();
    }
}
