<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\DTO\User\CreateUserDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for creating a new user.
 */
class CreateUserRequest extends FormRequest
{
    /**
     * Determine rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255,|unique:users,email',
            'password' => 'required|string',
            'roleId' => 'required|integer|exists:roles,id',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'The email has already been taken.',
            'roleId.exists' => 'The selected role does not exist.',
            'password.required' => 'The password field is required.',
            'username.required' => 'The username field is required.',
            'email.required' => 'The email field is required.',
            'roleId.required' => 'The role_id field is required.',
            'username.max' => 'The username may not be greater than 255 characters.',
            'email.max' => 'The email may not be greater than 255 characters.',
        ];
    }

    /**
     * @return \App\DTO\User\CreateUserDTO
     */
    public function toDTO(): CreateUserDTO
    {
        return new CreateUserDTO(
            username: $this->input('username'),
            email: $this->input('email'),
            password: $this->input('password'),
            roleId: $this->input('roleId'),
        );
    }
}
