<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\Auth\LoginDTO;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login' => 'required|max:255',
            'password' => 'required|string',
        ];
    }

    public function toDTO(): LoginDTO
    {
        return LoginDTO::fromArray($this->validated());
    }
}
