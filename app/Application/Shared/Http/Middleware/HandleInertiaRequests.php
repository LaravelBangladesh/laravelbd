<?php

namespace App\Application\Shared\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'version' => config('app.version'),
            'auth' => [
                'user' => $user === null ? null : [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'pending_email' => $user->pending_email,
                    'role' => $user->role->value,
                    'locale' => $user->locale,
                    'is_staff' => $user->isStaff(),
                    'is_admin' => $user->isAdmin(),
                    'photo_url' => $user->photoUrl(),
                ],
            ],
            'locale' => app()->getLocale(),
            'locales' => [
                'en' => 'English',
                'bn' => 'বাংলা',
            ],
            'translations' => $this->translations(),
            'seo' => [
                'url' => $request->url(),
                'default_image' => asset('images/og-default.webp'),
                'site_name' => config('app.name'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(): array
    {
        $path = lang_path(app()->getLocale().'.json');

        if (! File::exists($path)) {
            return [];
        }

        /** @var array<string, string> $translations */
        $translations = json_decode(File::get($path), true) ?? [];

        return $translations;
    }
}
