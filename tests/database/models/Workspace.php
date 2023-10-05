<?php

namespace WinLocalInc\Chjs\Tests\Database\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use WinLocalInc\Chjs\Concerns\HandlePreview;
use WinLocalInc\Chjs\Concerns\HandleSubscription;
use WinLocalInc\Chjs\Models\Subscription;

class Workspace extends Model
{
    use HandlePreview;
    use HandleSubscription;
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'workspace_id';

    protected $table = 'workspaces';

    protected $guarded = [];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'user_id');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'workspace_id');
    }
}
