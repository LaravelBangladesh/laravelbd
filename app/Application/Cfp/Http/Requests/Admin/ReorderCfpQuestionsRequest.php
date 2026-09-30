<?php

namespace App\Application\Cfp\Http\Requests\Admin;

use App\Domain\Events\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReorderCfpQuestionsRequest extends FormRequest
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
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['string', 'distinct'],
        ];
    }

    /**
     * @return list<\Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $event = $this->route('event');

                if (! $event instanceof Event || $validator->errors()->isNotEmpty()) {
                    return;
                }

                $expected = collect($event->cfp_questions ?? [])->pluck('id')->sort()->values();
                $actual = collect((array) $this->input('question_ids', []))->sort()->values();

                if ($expected->all() !== $actual->all()) {
                    $validator->errors()->add('question_ids', __('admin.question_order_invalid'));
                }
            },
        ];
    }
}
