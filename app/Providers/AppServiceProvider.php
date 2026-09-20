<?php

namespace App\Providers;

use App\Domain\Shared\Contracts\ImageStorage;
use App\Infrastructure\Auth\UuidEloquentUserProvider;
use App\Infrastructure\Images\CloudflareImageStorage;
use App\Infrastructure\Images\DiskImageStorage;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageStorage::class, function (): ImageStorage {
            $local = new DiskImageStorage((string) config('images.disk', 'public'));

            return match ((string) config('images.driver')) {
                'local' => $local,
                'r2' => new DiskImageStorage('r2'),
                default => new CloudflareImageStorage($local),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('eloquent', function ($app, array $config) {
            return new UuidEloquentUserProvider($app['hash'], $config['model']);
        });

        Factory::guessFactoryNamesUsing(
            /** @return class-string<Factory<Model>> */
            function (string $modelName): string {
                /** @var class-string<Factory<Model>> */
                return 'Database\\Factories\\'.class_basename($modelName).'Factory';
            },
        );

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
