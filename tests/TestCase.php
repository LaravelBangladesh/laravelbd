<?php

namespace Tests;

use App\Domain\Shared\Contracts\UrlShortener;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;
use Tests\Fakes\FakeUrlShortener;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(UrlShortener::class, new FakeUrlShortener);

        $this->withoutVite();
        $this->withHeaders([
            'Sec-Fetch-Site' => 'same-origin',
        ]);
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
