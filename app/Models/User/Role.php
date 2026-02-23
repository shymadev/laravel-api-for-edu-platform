<?php

declare(strict_types=1);

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const USER_ROLE_ID = 1;
    public const ADMIN_ROLE_ID = 2;
    public const MODERATOR_ROLE_ID = 3;

    protected $table = 'roles';

    protected $fillable = [
        'role_name',
    ];

    public $timestamps = false;

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
