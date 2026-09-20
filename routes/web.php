<?php

use App\Application\Cfp\Http\Controllers\EventProposalController;
use App\Application\Content\Http\Controllers\ResourceController;
use App\Application\Directory\Http\Controllers\Account\DirectoryProfileController;
use App\Application\Directory\Http\Controllers\DirectoryController;
use App\Application\Events\Http\Controllers\EventController;
use App\Application\Events\Http\Controllers\EventRsvpController;
use App\Application\Identity\Http\Controllers\Account\AccountController;
use App\Application\Identity\Http\Controllers\Account\CancelEmailChangeController;
use App\Application\Identity\Http\Controllers\Account\EmailChangeMagicLinkController;
use App\Application\Identity\Http\Controllers\Account\VerifyEmailChangeController;
use App\Application\Shared\Http\Controllers\AboutController;
use App\Application\Shared\Http\Controllers\HomeController;
use App\Application\Shared\Http\Controllers\LlmsTxtController;
use App\Application\Shared\Http\Controllers\LocaleController;
use App\Application\Shared\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('llms.txt', LlmsTxtController::class)->name('llms.txt');
Route::get('about', AboutController::class)->name('about');
Route::get('resources', [ResourceController::class, 'index'])->name('resources.index');
Route::get('resources/{resource:slug}', [ResourceController::class, 'show'])->name('resources.show');
Route::get('directory', [DirectoryController::class, 'index'])->name('directory.index');
Route::get('directory/{listing:slug}', [DirectoryController::class, 'show'])->name('directory.show');
Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('events/{event:slug}/cfp', [EventProposalController::class, 'create'])
    ->middleware('auth')
    ->name('events.cfp.create');
Route::post('events/{event:slug}/cfp', [EventProposalController::class, 'store'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('events.cfp.store');
Route::post('events/{event:slug}/rsvp', [EventRsvpController::class, 'store'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('events.rsvp.store');
Route::delete('events/{event:slug}/rsvp', [EventRsvpController::class, 'destroy'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('events.rsvp.destroy');

Route::post('locale', LocaleController::class)
    ->middleware('throttle:30,1')
    ->name('locale.update');

Route::middleware('auth')->group(function () {
    Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('account', [AccountController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('account.update');
    Route::get('account/directory', [DirectoryProfileController::class, 'edit'])->name('account.directory.edit');
    Route::post('account/directory', [DirectoryProfileController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('account.directory.store');
    Route::patch('account/directory', [DirectoryProfileController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('account.directory.update');
    Route::post('account/email/code', VerifyEmailChangeController::class)
        ->middleware('throttle:5,1')
        ->name('account.email.code');
    Route::post('account/email/cancel', CancelEmailChangeController::class)
        ->name('account.email.cancel');
});

Route::get('account/email/magic/{token}', [EmailChangeMagicLinkController::class, 'show'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('account.email.magic.show');
Route::post('account/email/magic/{token}', [EmailChangeMagicLinkController::class, 'store'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('account.email.magic.store');

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('account.edit'),
        'manage' => route('account.edit'),
    ]);
})->name('well-known.passkeys');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

Route::fallback(function () {
    return Inertia::render('errors/404')
        ->toResponse(request())
        ->setStatusCode(404);
});
