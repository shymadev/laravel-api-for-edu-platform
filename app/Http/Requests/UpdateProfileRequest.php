<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\Profile\UpdateProfileDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates profile update data.
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * @return boolean
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:512'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.string' => 'First name must be a string.',
            'first_name.max' => 'First name must not exceed 255 characters.',

            'last_name.string' => 'Last name must be a string.',
            'last_name.max' => 'Last name must not exceed 255 characters.',

            'bio.string' => 'Bio must be a string.',
            'bio.max' => 'Bio must not exceed 512 characters.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'bio' => 'bio',
        ];
    }

    /**
     * @return \App\DTO\Profile\UpdateProfileDTO
     */
    public function toDTO(): UpdateProfileDTO
    {
        return new UpdateProfileDTO(
            firstName: $this->input('first_name'),
            lastName: $this->input('last_name'),
            bio: $this->input('bio'),
        );
    }
}
