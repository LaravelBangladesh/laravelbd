<?php

namespace App\Application\Cfp\Http\Requests\Admin;

use App\Domain\Cfp\Enums\ProposalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTalkProposalRequest extends FormRequest
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
            'status' => ['required', Rule::enum(ProposalStatus::class)],
            'notes' => ['nullable', 'string'],
            'event_id' => ['required', 'uuid', 'exists:events,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_id.required' => __('cfp.event_required'),
        ];
    }
}
