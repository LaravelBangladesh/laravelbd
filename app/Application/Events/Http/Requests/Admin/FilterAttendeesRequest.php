<?php

namespace App\Application\Events\Http\Requests\Admin;

use App\Domain\Events\Enums\RegistrationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterAttendeesRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(RegistrationStatus::class)],
        ];
    }

    public function search(): ?string
    {
        $term = trim((string) $this->validated('q'));

        return $term === '' ? null : $term;
    }

    public function status(): ?RegistrationStatus
    {
        return RegistrationStatus::tryFrom((string) $this->validated('status'));
    }

    /**
     * The filters as the page echoes them back into its toolbar.
     *
     * @return array{q: string, status: string}
     */
    public function filters(): array
    {
        return [
            'q' => (string) $this->search(),
            'status' => (string) $this->status()?->value,
        ];
    }
}
