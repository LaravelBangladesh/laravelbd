<?php

namespace App\Application\Events\Http\Requests\Admin;

use App\Domain\Events\Enums\SpeakerRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachSpeakerRequest extends FormRequest
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
            'speaker_id' => ['required', 'uuid', 'exists:speakers,id'],
            'role' => ['required', Rule::enum(SpeakerRole::class)],
        ];
    }
}
