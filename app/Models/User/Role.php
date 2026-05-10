<?php

declare(strict_types=1);

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a user role in the platform (user, moderator, admin).
 */
class Role extends Model
{
    public const USER_ROLE_ID = 1;

    public const ADMIN_ROLE_ID = 2;

    public const MODERATOR_ROLE_ID = 3;

    public $timestamps = false;

    protected $table = 'roles';

    protected $fillable = [
        'role_name',
    ];

    /**
     * Return all users belonging to this role.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
