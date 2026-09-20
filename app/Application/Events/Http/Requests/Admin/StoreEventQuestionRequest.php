<?php

namespace App\Application\Events\Http\Requests\Admin;

use App\Domain\Events\Enums\QuestionKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEventQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() === true;
    }

    protected function prepareForValidation(): void
    {
        $options = $this->input('options');

        if (is_string($options)) {
            $this->merge([
                'options' => array_values(array_filter(
                    array_map(trim(...), preg_split('/\r\n|\r|\n/', $options) ?: []),
                    fn (string $line) => $line !== '',
                )),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kind' => ['required', Rule::enum(QuestionKind::class)],
            'label_en' => ['required', 'string', 'max:255'],
            'label_bn' => ['nullable', 'string', 'max:255'],
            'help_en' => ['nullable', 'string', 'max:1000'],
            'help_bn' => ['nullable', 'string', 'max:1000'],
            'required' => ['boolean'],
            'options' => ['nullable', 'array', 'max:50'],
            'options.*' => ['string', 'max:255'],
        ];
    }

    /**
     * @return list<\Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $kind = QuestionKind::from((string) $this->input('kind'));
                /** @var list<string> $options */
                $options = array_values(array_filter(
                    array_map(fn (mixed $option) => trim((string) $option), (array) $this->input('options', [])),
                    fn (string $option) => $option !== '',
                ));

                if (! $kind->isChoice()) {
                    if ($options !== []) {
                        $validator->errors()->add('options', __('admin.question_options_unsupported'));
                    }

                    return;
                }

                if (count(array_unique($options)) < 2) {
                    $validator->errors()->add('options', __('admin.question_options_required'));
                }
            },
        ];
    }
}
