<?php

namespace WinLocalInc\Chjs\Models;

use App\Models\Workspace\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use WinLocalInc\Chjs\Chjs;
use WinLocalInc\Chjs\Database\Factories\SubscriptionFactory;
use WinLocalInc\Chjs\Enums\MainComponent;
use WinLocalInc\Chjs\Enums\PaymentCollectionMethod;
use WinLocalInc\Chjs\Enums\SubscriptionInterval;
use WinLocalInc\Chjs\Enums\SubscriptionStatus;

/**
 * @property Workspace workspace
 * @property Product product
 * @property mixed $subscription_id
 * @property mixed $product_handle
 * @property mixed $component_handle
 */
class Subscription extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $primaryKey = 'subscription_id';

    protected $table = 'chjs_subscriptions';

    protected $guarded = [];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'product_handle' => \WinLocalInc\Chjs\Enums\Product::class,
        'payment_collection_method' => PaymentCollectionMethod::class,
        'subscription_interval' => SubscriptionInterval::class,
    ];

    public function getProductNameAttribute()
    {
        return $this->product_handle.'::'.$this->component_handle;
    }

    public function getMainComponentAtrribute()
    {
        return MainComponent::findComponent($this->component_handle);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Chjs::$subscriberModel, 'workspace_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Chjs::$userModel, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'product_id');
    }

    public function productPrice(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class, 'product_price_id');
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
