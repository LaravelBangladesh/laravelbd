<?php

use App\Application\Cfp\Http\Controllers\Admin\CfpQuestionController;
use App\Application\Cfp\Http\Controllers\Admin\TalkProposalController;
use App\Application\Content\Http\Controllers\Admin\ResourceController;
use App\Application\Directory\Http\Controllers\Admin\CompanyController;
use App\Application\Directory\Http\Controllers\Admin\DirectoryController;
use App\Application\Events\Http\Controllers\Admin\EventAttendeeController;
use App\Application\Events\Http\Controllers\Admin\EventController;
use App\Application\Events\Http\Controllers\Admin\EventMediaController;
use App\Application\Events\Http\Controllers\Admin\EventQuestionController;
use App\Application\Events\Http\Controllers\Admin\EventRegistrationController;
use App\Application\Events\Http\Controllers\Admin\EventReminderController;
use App\Application\Events\Http\Controllers\Admin\EventSessionController;
use App\Application\Events\Http\Controllers\Admin\EventSpeakerController;
use App\Application\Events\Http\Controllers\Admin\ReorderSessionsController;
use App\Application\Events\Http\Controllers\Admin\SessionSpeakerController;
use App\Application\Events\Http\Controllers\Admin\SpeakerController;
use App\Application\Identity\Http\Controllers\Admin\UserController;
use App\Application\Identity\Http\Controllers\Admin\UserRoleController;
use App\Application\Shared\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export');
    Route::get('users/{user}', [UserController::class, 'show'])->withTrashed()->name('users.show');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->withTrashed()->name('users.edit');
    Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('admin')
        ->name('users.destroy');
    Route::patch('users/{user}/restore', [UserController::class, 'restore'])
        ->withTrashed()
        ->middleware('admin')
        ->name('users.restore');
    Route::patch('users/{user}/role', UserRoleController::class)
        ->middleware('admin')
        ->name('users.role');

    Route::resource('events', EventController::class);
    Route::post('events/{event}/sessions', [EventSessionController::class, 'store'])->name('events.sessions.store');
    Route::patch('events/{event}/sessions/order', ReorderSessionsController::class)->name('events.sessions.reorder');
    Route::patch('events/{event}/sessions/{eventSession}', [EventSessionController::class, 'update'])->name('events.sessions.update');
    Route::delete('events/{event}/sessions/{eventSession}', [EventSessionController::class, 'destroy'])->name('events.sessions.destroy');
    Route::post('events/{event}/speakers', [EventSpeakerController::class, 'store'])->name('events.speakers.store');
    Route::delete('events/{event}/speakers/{speaker}', [EventSpeakerController::class, 'destroy'])->name('events.speakers.destroy');
    Route::post('events/{event}/sessions/{eventSession}/speakers', [SessionSpeakerController::class, 'store'])->name('events.sessions.speakers.store');
    Route::delete('events/{event}/sessions/{eventSession}/speakers/{speaker}', [SessionSpeakerController::class, 'destroy'])->name('events.sessions.speakers.destroy');
    Route::post('events/{event}/media', [EventMediaController::class, 'store'])->name('events.media.store');
    Route::delete('events/{event}/media/{medium}', [EventMediaController::class, 'destroy'])->name('events.media.destroy');
    Route::get('events/{event}/attendees', [EventAttendeeController::class, 'index'])->name('events.attendees.index');
    Route::get('events/{event}/attendees/export', [EventAttendeeController::class, 'export'])->name('events.attendees.export');
    Route::delete('events/{event}/registrations/{registration}', [EventRegistrationController::class, 'destroy'])->name('events.registrations.destroy');
    Route::patch('events/{event}/registrations/{registration}/restore', [EventRegistrationController::class, 'restore'])->name('events.registrations.restore');
    Route::get('events/{event}/reminders/preview', [EventReminderController::class, 'preview'])->name('events.reminders.preview');
    Route::post('events/{event}/reminders', [EventReminderController::class, 'store'])->name('events.reminders.store');
    Route::post('events/{event}/registrations/{registration}/reminder', [EventReminderController::class, 'resend'])->name('events.registrations.reminder');
    Route::post('events/{event}/questions', [EventQuestionController::class, 'store'])->name('events.questions.store');
    Route::patch('events/{event}/questions/order', [EventQuestionController::class, 'reorder'])->name('events.questions.reorder');
    Route::patch('events/{event}/questions/{question}', [EventQuestionController::class, 'update'])->name('events.questions.update');
    Route::delete('events/{event}/questions/{question}', [EventQuestionController::class, 'destroy'])->name('events.questions.destroy');
    Route::post('events/{event}/cfp-questions', [CfpQuestionController::class, 'store'])->name('events.cfp-questions.store');
    Route::patch('events/{event}/cfp-questions/order', [CfpQuestionController::class, 'reorder'])->name('events.cfp-questions.reorder');
    Route::patch('events/{event}/cfp-questions/{question}', [CfpQuestionController::class, 'update'])->name('events.cfp-questions.update');
    Route::delete('events/{event}/cfp-questions/{question}', [CfpQuestionController::class, 'destroy'])->name('events.cfp-questions.destroy');

    Route::resource('speakers', SpeakerController::class)->only(['index', 'create', 'store']);
    Route::resource('resources', ResourceController::class)->except(['show']);
    Route::get('directory', DirectoryController::class)->name('directory.index');
    Route::resource('directory', CompanyController::class)
        ->parameters(['directory' => 'company'])
        ->except(['index', 'show']);
    Route::resource('proposals', TalkProposalController::class)->only(['index', 'show', 'update']);
});
