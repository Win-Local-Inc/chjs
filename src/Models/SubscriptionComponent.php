<?php

namespace WinLocalInc\Chjs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $component_id
 */
class SubscriptionComponent extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $primaryKey = 'subscription_component_id';

    protected $table = 'chjs_subscription_components';

    protected $guarded = [];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

    public function componentPrice(): BelongsTo
    {
        return $this->belongsTo(ComponentPrice::class, 'component_price_id');
    }
}
