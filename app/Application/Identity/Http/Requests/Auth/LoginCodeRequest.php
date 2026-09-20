<?php

namespace App\Application\Identity\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ];
    }
}
