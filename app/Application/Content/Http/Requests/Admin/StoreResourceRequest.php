<?php

namespace App\Application\Content\Http\Requests\Admin;

use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;
use App\Domain\Shared\Rules\YouTubeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResourceRequest extends FormRequest
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
        $kind = $this->enum('kind', ResourceKind::class);

        return [
            'title_en' => ['required', 'string', 'max:255'],
            'title_bn' => ['nullable', 'string', 'max:255'],
            'excerpt_en' => ['nullable', 'string', 'max:500'],
            'excerpt_bn' => ['nullable', 'string', 'max:500'],
            'description_en' => ['nullable', 'string'],
            'description_bn' => ['nullable', 'string'],
            'kind' => ['required', Rule::enum(ResourceKind::class)],
            'status' => ['required', Rule::enum(ResourceStatus::class)],
            'url' => [
                $kind?->needsVideo() === true ? 'nullable' : 'required',
                'url',
                'max:500',
            ],
            'embed_url' => [
                $kind?->needsVideo() === true ? 'required' : 'nullable',
                'url',
                'max:500',
                new YouTubeUrl,
            ],
            'event_id' => ['nullable', 'uuid', 'exists:events,id'],
            'speaker_id' => ['nullable', 'uuid', 'exists:speakers,id'],
        ];
    }
}
