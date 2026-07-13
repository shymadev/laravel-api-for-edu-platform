<?php

declare(strict_types=1);

namespace App\Http\Requests\Advertisement;

use App\DTO\Advertisement\UpdateAdvertisementDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates advertisement update data.
 */
class UpdateAdvertisementRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'url' => ['sometimes', 'string', 'url', 'max:500'],
            'is_active' => ['sometimes', 'string'],
            'is_permanent' => ['sometimes', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ];
    }

    /**
     * @return \App\DTO\Advertisement\UpdateAdvertisementDTO
     */
    public function toDTO(): UpdateAdvertisementDto
    {
        return new UpdateAdvertisementDTO(
            id: (int) $this->route('advertisement')->id,
            image: $this->file('image'),
            url: $this->input('url'),
            isActive: $this->has('is_active') ? $this->input('is_active') === 'true' : null,
            startsAt: $this->input('starts_at'),
            endsAt: $this->input('ends_at'),
            isPermanent: $this->has('is_permanent') ? $this->input('is_permanent') === 'true' : null,
        );
    }
}
