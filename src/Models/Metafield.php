<?php

namespace WinLocalInc\Chjs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use WinLocalInc\Chjs\Database\Factories\MetafieldFactory;

class Metafield extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $table = 'chjs_metafields';

    protected $guarded = [];

    protected $casts = [
    ];

    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class, 'chjs_metafield_subscription', 'metafield_id', 'workspace_id');
    }

    protected static function newFactory()
    {
        return MetafieldFactory::new();
    }
}
