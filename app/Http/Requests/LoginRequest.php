<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\Auth\LoginDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates login request data.
 */
class LoginRequest extends FormRequest
{
    /**
     * Return the validation rules for this request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'login' => 'required|max:255',
            'password' => 'required|string',
        ];
    }

    /**
     * Convert validated data to a LoginDTO.
     *
     * @return \App\DTO\Auth\LoginDTO
     */
    public function toDTO(): LoginDTO
    {
        return LoginDTO::fromArray($this->validated());
    }
}
