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
     * Each answer is a string (text and single choice) or a list of strings
     * (multiple choice); the action checks the value against its question.
     *
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
            'answers' => ['nullable', 'array', 'max:50'],
            'answers.*' => ['nullable', 'max:2000'],
            'answers.*.*' => ['string', 'max:2000'],
        ];
    }
}
