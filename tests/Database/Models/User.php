<?php

namespace WinLocalInc\Chjs\Tests\Database\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use WinLocalInc\Chjs\Concerns\HandleCustomer;
use WinLocalInc\Chjs\Tests\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HandleCustomer;
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id',
        'firstname',
        'lastname',
        'email',
        'workspace_id',
        'chargify_id',
    ];

    public function activeWorkspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
