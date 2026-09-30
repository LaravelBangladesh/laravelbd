<?php

namespace App\Application\Cfp\Http\Controllers\Admin;

use App\Application\Cfp\Http\Requests\Admin\ReorderCfpQuestionsRequest;
use App\Application\Events\Http\Requests\Admin\StoreEventQuestionRequest;
use App\Application\Events\Http\Requests\Admin\UpdateEventQuestionRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Cfp\Actions\CreateCfpQuestion;
use App\Domain\Cfp\Actions\DeleteCfpQuestion;
use App\Domain\Cfp\Actions\ReorderCfpQuestions;
use App\Domain\Cfp\Actions\UpdateCfpQuestion;
use App\Domain\Events\Data\EventQuestionData;
use App\Domain\Events\Models\Event;
use Illuminate\Http\RedirectResponse;

class CfpQuestionController extends Controller
{
    public function store(StoreEventQuestionRequest $request, Event $event, CreateCfpQuestion $create): RedirectResponse
    {
        $this->authorize('update', $event);

        $create($event, EventQuestionData::fromValidated($request->validated()));

        return back();
    }

    public function update(UpdateEventQuestionRequest $request, Event $event, string $question, UpdateCfpQuestion $update): RedirectResponse
    {
        $this->authorize('update', $event);

        $this->ensureQuestion($event, $question);

        $update($event, $question, EventQuestionData::fromValidated($request->validated()));

        return back();
    }

    public function destroy(Event $event, string $question, DeleteCfpQuestion $delete): RedirectResponse
    {
        $this->authorize('update', $event);

        $this->ensureQuestion($event, $question);

        $delete($event, $question);

        return back();
    }

    public function reorder(ReorderCfpQuestionsRequest $request, Event $event, ReorderCfpQuestions $reorder): RedirectResponse
    {
        $this->authorize('update', $event);

        /** @var list<string> $questionIds */
        $questionIds = $request->validated('question_ids');

        $reorder($event, $questionIds);

        return back();
    }

    private function ensureQuestion(Event $event, string $questionId): void
    {
        abort_unless(collect($event->cfp_questions ?? [])->contains('id', $questionId), 404);
    }
}
