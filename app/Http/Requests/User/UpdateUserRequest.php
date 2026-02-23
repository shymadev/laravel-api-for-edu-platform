<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\DTO\User\UpdateUserDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for updating an existing user.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => 'string|max:255',
            'email' => 'email|max:255,|unique:users,email,' . $this->route('user')->id,
            'password' => 'string|nullable',
            'roleId' => 'integer|exists:roles,id',
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
            'username.max' => 'The username may not be greater than 255 characters.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'password.string' => 'The password must be a string.',
            'roleId.integer' => 'The role_id must be an integer.',
        ];
    }

    public function toDTO(): UpdateUserDTO
    {
        return new UpdateUserDTO(
            username: $this->input('username'),
            email: $this->input('email'),
            password: $this->input('password'),
            roleId: $this->input('roleId'),
        );
    }
}
