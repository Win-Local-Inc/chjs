<?php

namespace WinLocalInc\Chjs\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Workspace\Workspace;

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
