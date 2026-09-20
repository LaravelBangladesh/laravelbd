<?php

use App\Application\Cfp\Http\Controllers\Admin\TalkProposalController;
use App\Application\Content\Http\Controllers\Admin\ResourceController;
use App\Application\Directory\Http\Controllers\Admin\DirectoryListingController;
use App\Application\Events\Http\Controllers\Admin\EventController;
use App\Application\Events\Http\Controllers\Admin\EventMediaController;
use App\Application\Events\Http\Controllers\Admin\EventQuestionController;
use App\Application\Events\Http\Controllers\Admin\EventRegistrationController;
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
    Route::delete('events/{event}/registrations/{registration}', [EventRegistrationController::class, 'destroy'])->name('events.registrations.destroy');
    Route::post('events/{event}/questions', [EventQuestionController::class, 'store'])->name('events.questions.store');
    Route::patch('events/{event}/questions/order', [EventQuestionController::class, 'reorder'])->name('events.questions.reorder');
    Route::patch('events/{event}/questions/{question}', [EventQuestionController::class, 'update'])->name('events.questions.update');
    Route::delete('events/{event}/questions/{question}', [EventQuestionController::class, 'destroy'])->name('events.questions.destroy');

    Route::resource('speakers', SpeakerController::class)->except(['show']);
    Route::resource('resources', ResourceController::class)->except(['show']);
    Route::resource('directory', DirectoryListingController::class)
        ->parameters(['directory' => 'listing'])
        ->except(['show']);
    Route::resource('proposals', TalkProposalController::class)->only(['index', 'show', 'update']);
});
