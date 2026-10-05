<?php

namespace App\Application\Identity\Http\Requests;

use App\Infrastructure\Images\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use Throwable;

/**
 * The profile a person shares across the site, edited by its owner or staff.
 * The mobile number arrives in the national format of the chosen country and
 * is rewritten to E.164 first, so uniqueness compares like with like.
 */
abstract class ProfileDetailsRequest extends FormRequest
{
    public const DEFAULT_COUNTRY = 'BD';

    abstract protected function profileOwnerId(): string;

    protected function prepareForValidation(): void
    {
        $country = strtoupper((string) ($this->input('mobile_number_country') ?: self::DEFAULT_COUNTRY));
        $number = $this->input('mobile_number');

        $this->merge([
            'mobile_number_country' => $country,
            'mobile_number' => is_string($number) ? self::e164($number, $country) : $number,
        ]);
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
            'city' => ['nullable', 'string', 'max:255'],
            'bio_en' => ['nullable', 'string'],
            'bio_bn' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'x' => ['nullable', 'string', 'max:255'],
            'photo' => ImageUpload::rules(),
            'mobile_number' => [
                'nullable',
                'string',
                'max:32',
                (new Phone)->mobile()->countryField('mobile_number_country'),
                Rule::unique('users', 'mobile_number')->ignore($this->profileOwnerId()),
            ],
            'mobile_number_country' => ['required', 'string', 'size:2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile_number.unique' => __('validation.mobile_taken'),
        ];
    }

    private static function e164(string $number, string $country): string
    {
        try {
            return (new PhoneNumber($number, $country))->formatE164();
        } catch (Throwable) {
            return $number;
        }
    }
}
