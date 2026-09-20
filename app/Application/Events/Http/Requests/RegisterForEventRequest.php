<?php

namespace App\Application\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterForEventRequest extends FormRequest
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
            'answers' => ['nullable', 'array', 'max:50'],
            'answers.*' => ['nullable', 'max:2000'],
            'answers.*.*' => ['string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function answers(): array
    {
        /** @var array<string, mixed> $answers */
        $answers = (array) ($this->validated('answers') ?? []);

        return $answers;
    }
}
