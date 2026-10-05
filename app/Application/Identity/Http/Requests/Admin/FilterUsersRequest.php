<?php

namespace App\Application\Identity\Http\Requests\Admin;

use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterUsersRequest extends FormRequest
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
            'role' => ['nullable', Rule::enum(UserRole::class)],
            'directory_status' => ['nullable', Rule::enum(DirectoryVisibility::class)],
            'speaker' => ['nullable', Rule::in(['yes', 'no'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'all'])],
        ];
    }

    public function search(): ?string
    {
        $term = trim((string) $this->validated('q'));

        return $term === '' ? null : $term;
    }

    public function role(): ?UserRole
    {
        return UserRole::tryFrom((string) $this->validated('role'));
    }

    public function directoryStatus(): ?DirectoryVisibility
    {
        return DirectoryVisibility::tryFrom((string) $this->validated('directory_status'));
    }

    public function speaker(): ?bool
    {
        $speaker = $this->validated('speaker');

        return $speaker === null ? null : $speaker === 'yes';
    }

    /**
     * Active users unless the request asks for the deactivated ones or all.
     */
    public function status(): string
    {
        return (string) ($this->validated('status') ?? 'active');
    }

    /**
     * The filters as the page echoes them back into its toolbar.
     *
     * @return array{q: string, role: string, directory_status: string, speaker: string, status: string}
     */
    public function filters(): array
    {
        return [
            'q' => (string) $this->search(),
            'role' => (string) $this->role()?->value,
            'directory_status' => (string) $this->directoryStatus()?->value,
            'speaker' => (string) $this->validated('speaker'),
            'status' => (string) $this->validated('status'),
        ];
    }
}
