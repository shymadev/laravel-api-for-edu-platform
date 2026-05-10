<?php

declare(strict_types=1);

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Represents a user's public profile information.
 */
class Profile extends Model
{
    protected $table = 'profiles';

    protected $fillable = [
        'first_name',
        'last_name',
        'bio',
        'avatar_url',
    ];

    /**
     * Return the user that owns this profile.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'profile_id', 'id');
    }
}
