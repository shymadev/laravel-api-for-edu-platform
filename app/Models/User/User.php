<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Additional\Log;
use App\Models\Education\FavoritePhrase;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;
    use Billable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'password_hash',
        'email',
        'google_id',
        'created_at',
        'updated_at',
        'is_blocked',
        'role_id',
        'profile_id',
    ];

    protected $attributes = [
        'role_id' => Role::USER_ROLE_ID,
    ];

    protected $hidden = [
        'password_hash',
    ];

    public $timestamps = false;

    protected $casts = [
        'is_blocked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'id', 'profile_id');
    }

    public function completedLessons(): HasMany
    {
        return $this->hasMany(UserCompletedLesson::class, 'user_id');
    }

    public function courseStats(): HasMany
    {
        return $this->hasMany(UserCourseStatistics::class, 'user_id');
    }

    public function favoritePhrases(): HasMany
    {
        return $this->hasMany(FavoritePhrase::class, 'user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'user_id');
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    protected static function boot(): void
    {
        parent::boot();

        User::creating(function ($user) {
            if ($user->created_at === null) {
                $user->created_at = now();
            }
        });

        User::updating(function ($user) {
            if ($user->updated_at === null) {
                $user->updated_at = now();
            }
        });
    }
}
