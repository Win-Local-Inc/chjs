<?php

namespace WinLocalInc\Chjs\Tests\Database\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WinLocalInc\Chjs\Concerns\HandleSubscription;

class Workspace extends Model
{
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
}
