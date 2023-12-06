<?php

namespace WinLocalInc\Chjs\Models;

use App\Models\Workspace\Workspace;
use Illuminate\Database\Eloquent\Model;

class MetafieldSubscription extends Model
{
    protected $primaryKey = 'workspace_id';

    protected $table = 'chjs_metafield_subscription';

    protected $guarded = [];

    protected $casts = [
    ];

    public function workspace(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
}
