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
     * {@inheritdoc}
     */
    public function messages()
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
