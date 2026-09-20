<?php

namespace App\Application\Events\Http\Requests\Admin;

use App\Domain\Events\Enums\SpeakerRole;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignSessionSpeakerRequest extends FormRequest
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
            'speaker_source' => ['required', Rule::in(['existing', 'new'])],
            'speaker_id' => ['required_if:speaker_source,existing', 'nullable', 'uuid', 'exists:speakers,id'],
            'speaker_role' => ['required', Rule::enum(SpeakerRole::class)],
            'speaker_name' => ['required_if:speaker_source,new', 'nullable', 'string', 'max:255'],
            'speaker_title' => ['nullable', 'string', 'max:255'],
            'speaker_company' => ['nullable', 'string', 'max:255'],
            'speaker_photo' => ImageUpload::rules(),
        ];
    }
}
