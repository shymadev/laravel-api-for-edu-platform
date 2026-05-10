<?php

declare(strict_types=1);

namespace App\Http\Requests\Advertisement;

use App\DTO\Advertisement\CreateAdvertisementDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates advertisement creation data.
 */
class CreateAdvertisementRequest extends FormRequest
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
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'url' => ['required', 'string', 'url', 'max:500'],
            'is_active' => ['sometimes', 'string'],
            'starts_at' => ['nullable', 'date', 'after_or_equal:now'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ];
    }

    /**
     * @return \App\DTO\Advertisement\CreateAdvertisementDTO
     */
    public function toDTO(): CreateAdvertisementDto
    {
        return new CreateAdvertisementDTO(
            image: $this->file('image'),
            url: $this->input('url'),
            isActive: $this->input('is_active', 'true') === 'true',
            startsAt: $this->input('starts_at'),
            endsAt: $this->input('ends_at'),
        );
    }
}
