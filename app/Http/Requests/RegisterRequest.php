<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\User\CreateUserDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates user registration request data.
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return boolean
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Return the validation rules for this request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * Return custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Пользователь с таким email уже существует.',
        ];
    }

    /**
     * Convert validated data to a CreateUserDTO.
     *
     * @return \App\DTO\User\CreateUserDTO
     */
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
