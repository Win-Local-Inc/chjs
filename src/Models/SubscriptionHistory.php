<?php

namespace WinLocalInc\Chjs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WinLocalInc\Chjs\Chjs;

class SubscriptionHistory extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $table = 'chjs_subscription_histories';

    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Chjs::$subscriberModel, 'workspace_id');
    }

    public static function createSubscriptionHistory(string $subscriptionId, string $action): void
    {
        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::where(['subscription_id' => $subscriptionId])->with('subscriptionComponents')->first();

        if (! $subscription) {
            return;
        }

        SubscriptionHistory::create(
            [
                'workspace_id' => $subscription->workspace_id,
                'subscription_id' => $subscription->subscription_id,
                'action' => $action,
                'status' => $subscription->status,
                'data' => $subscription,
            ]
        );
    }
}
