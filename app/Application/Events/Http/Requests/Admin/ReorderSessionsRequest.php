<?php

namespace App\Application\Events\Http\Requests\Admin;

use App\Domain\Events\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReorderSessionsRequest extends FormRequest
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
        $event = $this->route('event');

        return [
            'session_ids' => ['required', 'array', 'min:1'],
            'session_ids.*' => [
                'uuid',
                Rule::exists('event_sessions', 'id')->where(
                    fn ($query) => $query->where('event_id', $event instanceof Event ? $event->id : null),
                ),
            ],
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

                $expected = $event->sessions()->pluck('id')->sort()->values();
                $actual = collect((array) $this->input('session_ids', []))->sort()->values();

                if ($expected->all() !== $actual->all()) {
                    $validator->errors()->add('session_ids', __('admin.session_order_invalid'));
                }
            },
        ];
    }
}
