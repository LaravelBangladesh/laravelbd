<?php

namespace App\Application\Events\Http\Requests\Admin;

use App\Domain\Events\Enums\MediaKind;
use App\Domain\Shared\Rules\YouTubeUrl;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEventMediaRequest extends FormRequest
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
            'kind' => ['required', Rule::enum(MediaKind::class)],
            'photo' => ImageUpload::rules(),
            'embed_url' => ['nullable', 'url', 'max:500', new YouTubeUrl],
            'caption_en' => ['nullable', 'string', 'max:255'],
            'caption_bn' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return list<\Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $kind = $this->enum('kind', MediaKind::class);

                if ($kind === MediaKind::Photo && ! $this->hasFile('photo')) {
                    $validator->errors()->add('photo', __('events.media.photo_required'));
                }

                if ($kind === MediaKind::Video && ! $this->filled('embed_url')) {
                    $validator->errors()->add('embed_url', __('events.media.youtube_required'));
                }
            },
        ];
    }
}
