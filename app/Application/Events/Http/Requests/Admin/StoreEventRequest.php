<?php

namespace App\Application\Events\Http\Requests\Admin;

use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title_en' => ['required', 'string', 'max:255'],
            'title_bn' => ['nullable', 'string', 'max:255'],
            'excerpt_en' => ['nullable', 'string', 'max:500'],
            'excerpt_bn' => ['nullable', 'string', 'max:500'],
            'description_en' => ['nullable', 'string'],
            'description_bn' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(EventType::class)],
            'status' => ['required', Rule::enum(EventStatus::class)],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'venue_map_url' => ['nullable', 'url', 'max:500'],
            'online_url' => ['nullable', 'url', 'max:500'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'registration_enabled' => ['boolean'],
            'cfp_enabled' => ['boolean'],
            'cfp_opens_at' => ['nullable', 'date'],
            'cfp_closes_at' => [
                'nullable',
                'date',
                Rule::when(
                    fn () => $this->filled('cfp_opens_at'),
                    ['after:cfp_opens_at'],
                ),
            ],
            'cover' => ImageUpload::rules(),
        ];
    }
}
