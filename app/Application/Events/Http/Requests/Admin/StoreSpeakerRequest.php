<?php

namespace App\Application\Events\Http\Requests\Admin;

use App\Infrastructure\Images\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpeakerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'bio_en' => ['nullable', 'string'],
            'bio_bn' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'x' => ['nullable', 'string', 'max:255'],
            'photo' => ImageUpload::rules(),
        ];
    }
}
