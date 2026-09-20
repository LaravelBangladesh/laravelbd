<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Events\Http\Requests\Admin\ReorderEventQuestionsRequest;
use App\Application\Events\Http\Requests\Admin\StoreEventQuestionRequest;
use App\Application\Events\Http\Requests\Admin\UpdateEventQuestionRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Actions\CreateEventQuestion;
use App\Domain\Events\Actions\DeleteEventQuestion;
use App\Domain\Events\Actions\ReorderEventQuestions;
use App\Domain\Events\Actions\UpdateEventQuestion;
use App\Domain\Events\Data\EventQuestionData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;
use Illuminate\Http\RedirectResponse;

class EventQuestionController extends Controller
{
    public function store(StoreEventQuestionRequest $request, Event $event, CreateEventQuestion $create): RedirectResponse
    {
        $this->authorize('update', $event);

        $create($event, EventQuestionData::fromValidated($request->validated()));

        return back();
    }

    public function update(UpdateEventQuestionRequest $request, Event $event, EventQuestion $question, UpdateEventQuestion $update): RedirectResponse
    {
        $this->authorize('update', $event);

        abort_unless($question->event_id === $event->id, 404);

        $update($question, EventQuestionData::fromValidated($request->validated()));

        return back();
    }

    public function destroy(Event $event, EventQuestion $question, DeleteEventQuestion $delete): RedirectResponse
    {
        $this->authorize('update', $event);

        abort_unless($question->event_id === $event->id, 404);

        $delete($question);

        return back();
    }

    public function reorder(ReorderEventQuestionsRequest $request, Event $event, ReorderEventQuestions $reorder): RedirectResponse
    {
        $this->authorize('update', $event);

        /** @var list<string> $questionIds */
        $questionIds = $request->validated('question_ids');

        $reorder($event, $questionIds);

        return back();
    }
}
