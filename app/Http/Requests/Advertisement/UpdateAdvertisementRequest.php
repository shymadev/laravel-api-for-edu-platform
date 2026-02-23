<?php

declare(strict_types=1);

namespace App\Http\Requests\Advertisement;

use App\DTO\Advertisement\UpdateAdvertisementDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvertisementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ];
    }

    public function toDTO(): UpdateAdvertisementDto
    {
        return new UpdateAdvertisementDTO(
            id: (int) $this->route('advertisement')->id,
            image: $this->file('image'),
            url: $this->input('url'),
            isActive: $this->input('is_active', 'true') === 'true',
            startsAt: $this->input('starts_at'),
            endsAt: $this->input('ends_at'),
        );
    }
}
