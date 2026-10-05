<?php

namespace App\Application\Identity\Http\Requests\Admin;

use App\Application\Identity\Http\Requests\ProfileDetailsRequest;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Models\User;
use Illuminate\Validation\Rule;

class UpdateUserProfileRequest extends ProfileDetailsRequest
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
            ...parent::rules(),
            'directory_status' => ['required', Rule::enum(DirectoryVisibility::class)],
        ];
    }

    protected function profileOwnerId(): string
    {
        $user = $this->route('user');

        return $user instanceof User ? $user->id : '';
    }
}
