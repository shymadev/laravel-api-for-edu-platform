<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

class LoginUser
{
    public function execute(array $credentials): bool
    {
        return Auth::attempt($credentials);
    }
}
