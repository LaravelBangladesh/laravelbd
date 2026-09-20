<?php

namespace App\Application\Cfp\Http\Requests;

use App\Domain\Cfp\Enums\ProposalKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title_en' => ['required', 'string', 'max:255'],
            'title_bn' => ['nullable', 'string', 'max:255'],
            'abstract_en' => ['required', 'string'],
            'abstract_bn' => ['nullable', 'string'],
            'kind' => ['required', Rule::enum(ProposalKind::class)],
        ];
    }
}
