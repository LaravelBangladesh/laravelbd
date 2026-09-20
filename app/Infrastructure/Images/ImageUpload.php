<?php

namespace App\Infrastructure\Images;

use App\Domain\Shared\Data\UploadedImage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ImageUpload
{
    public const MAX_KILOBYTES = 51200;

    /**
     * @var list<string>
     */
    public const MIMES = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'avif'];

    /**
     * @return list<string>
     */
    public static function rules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:'.implode(',', self::MIMES),
            'max:'.self::MAX_KILOBYTES,
        ];
    }

    public static function from(Request $request, string $key): ?UploadedImage
    {
        $file = $request->file($key);

        if (! $file instanceof UploadedFile) {
            return null;
        }

        return new UploadedImage($file->getContent(), $file->getClientOriginalName());
    }
}
