<?php

namespace App\Application\Shared\Http;

use App\Domain\Identity\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

/**
 * Sends a member with an incomplete profile to the directory profile editor and
 * remembers where they were heading. Only a route name and an event slug are
 * stored, so the destination can never point off-site.
 */
class ProfileGate
{
    private const SESSION_KEY = 'profile.return_to';

    /**
     * @var array<string, string>
     */
    private const LABELS = [
        'events.show' => 'profile.return_to.event',
        'events.cfp.create' => 'profile.return_to.cfp',
    ];

    public static function redirect(string $routeName, string $slug): RedirectResponse
    {
        session([self::SESSION_KEY => ['route' => $routeName, 'slug' => $slug]]);

        Inertia::flash('toast', [
            'type' => 'error',
            'message' => __('profile.incomplete'),
        ]);

        return to_route('account.directory.edit');
    }

    /**
     * @return array{label: string}|null
     */
    public static function pending(): ?array
    {
        $pending = self::stored();

        return $pending === null
            ? null
            : ['label' => __(self::LABELS[$pending['route']])];
    }

    public static function resume(User $user): ?RedirectResponse
    {
        $pending = self::stored();

        if ($pending === null || ! $user->hasCompleteProfile()) {
            return null;
        }

        session()->forget(self::SESSION_KEY);

        return to_route($pending['route'], ['event' => $pending['slug']]);
    }

    /**
     * @return array{route: string, slug: string}|null
     */
    private static function stored(): ?array
    {
        /** @var array{route: string, slug: string}|null $pending */
        $pending = session(self::SESSION_KEY);

        return $pending !== null && isset(self::LABELS[$pending['route']])
            ? $pending
            : null;
    }
}
