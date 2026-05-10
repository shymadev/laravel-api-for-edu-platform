<?php

declare(strict_types=1);

namespace App\Models\User;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * Represents a Sanctum personal access token extended for this application.
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
}
