<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Additional\Log;
use App\Models\Education\FavoritePhrase;
use App\Observers\UserObserver;
use App\Services\MailService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Represents an authenticated user in the platform.
 */
#[ObservedBy(UserObserver::class)]
class User extends Authenticatable
{
    use Billable;
    use HasApiTokens;
    use Notifiable;

    public $timestamps = false;

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

    protected $casts = [
        'is_blocked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Return the role this user belongs to.
     *
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Return the profile this user belongs to.
     *
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    /**
     * Return all completed lessons belonging to this user.
     *
     * @return HasMany
     */
    public function completedLessons(): HasMany
    {
        return $this->hasMany(UserCompletedLesson::class, 'user_id');
    }

    /**
     * Return all course statistics belonging to this user.
     *
     * @return HasMany
     */
    public function courseStats(): HasMany
    {
        return $this->hasMany(UserCourseStatistics::class, 'user_id');
    }

    /**
     * Return all favorite phrases belonging to this user.
     *
     * @return HasMany
     */
    public function favoritePhrases(): HasMany
    {
        return $this->hasMany(FavoritePhrase::class, 'user_id');
    }

    /**
     * Return all logs belonging to this user.
     *
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'user_id');
    }

    /**
     * Return the hashed password used for authentication.
     *
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Determine if the user has a locally stored password (non-OAuth account).
     *
     * @return boolean
     */
    public function hasLocalPassword(): bool
    {
        return filled($this->password_hash);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $token
     *
     * @return void
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        app(MailService::class)->sendTemplate(
            $this->getEmailForPasswordReset(),
            'Сброс пароля — Tallksy',
            'mail.password-reset',
            [
                'user' => $this,
                'resetUrl' => $this->passwordResetUrl($token),
            ],
        );
    }

    /**
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        User::creating(function ($user): void {
            if ($user->created_at === null) {
                $user->created_at = now();
            }
        });

        User::updating(function ($user): void {
            if ($user->updated_at === null) {
                $user->updated_at = now();
            }
        });
    }

    /**
     * Generate the password reset URL.
     *
     * @param string $token
     *
     * @return string
     */
    protected function passwordResetUrl(string $token): string
    {
        $base = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return $base . '/reset-password?' . http_build_query([
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ]);
    }
}
