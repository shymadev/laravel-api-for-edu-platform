<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\User\CreateUserDTO;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Пользователь с таким email уже существует.',
        ];
    }

    public function toDTO(): CreateUserDTO
    {
        return new CreateUserDTO(
            username: $this->input('name'),
            email: $this->input('email'),
            password: $this->input('password'),
            roleId: 1,
        );
    }
}
