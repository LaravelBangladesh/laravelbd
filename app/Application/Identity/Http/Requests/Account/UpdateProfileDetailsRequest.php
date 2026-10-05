<?php

namespace App\Application\Identity\Http\Requests\Account;

use App\Application\Identity\Http\Requests\ProfileDetailsRequest;

class UpdateProfileDetailsRequest extends ProfileDetailsRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function profileOwnerId(): string
    {
        return (string) $this->user()?->id;
    }
}
