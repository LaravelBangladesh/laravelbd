<?php

namespace App\Application\Events\Http\Requests\Admin;

use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Shared\Rules\YouTubeUrl;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSessionRequest extends FormRequest
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
            'description_en' => ['nullable', 'string'],
            'description_bn' => ['nullable', 'string'],
            'kind' => ['required', Rule::enum(SessionKind::class)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'room' => ['nullable', 'string', 'max:255'],
            'recording_url' => ['nullable', 'url', 'max:500', new YouTubeUrl],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'speaker_source' => ['nullable', Rule::in(['none', 'existing', 'new'])],
            'speaker_id' => ['required_if:speaker_source,existing', 'nullable', 'uuid', 'exists:speakers,id'],
            'speaker_role' => ['nullable', Rule::enum(SpeakerRole::class)],
            'speaker_name' => ['required_if:speaker_source,new', 'nullable', 'string', 'max:255'],
            'speaker_title' => ['nullable', 'string', 'max:255'],
            'speaker_company' => ['nullable', 'string', 'max:255'],
            'speaker_photo' => ImageUpload::rules(),
        ];
    }
}
