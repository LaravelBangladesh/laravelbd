<?php

namespace App\Application\Identity\Http\Requests\Account;

use App\Domain\Identity\Enums\DirectoryVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A hidden member may ask to be listed; anyone pending or listed may hide.
 * Listing itself is left to staff.
 */
class UpdateDirectoryVisibilityRequest extends FormRequest
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
        $allowed = $this->user()?->directory_status === DirectoryVisibility::Hidden
            ? DirectoryVisibility::Pending
            : DirectoryVisibility::Hidden;

        return [
            'visibility' => ['required', Rule::in([$allowed->value])],
        ];
    }
}
