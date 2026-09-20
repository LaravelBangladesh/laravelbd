<?php

use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Infrastructure\Images\CloudflareImageStorage;
use App\Infrastructure\Images\DiskImageStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

arch()->preset()->php();

arch()->preset()->laravel()
    ->ignoring([
        // tightened in architecture step 3: the laravel preset hardcodes
        // App\Models and App\Enums as the only homes for models and enums,
        // which the modular layout no longer uses.
        'App\Domain\Identity\Models',
        'App\Domain\Events\Models',
        'App\Domain\Cfp\Models',
        'App\Domain\Directory\Models',
        'App\Domain\Content\Models',
        'App\Domain\Identity\Enums',
        'App\Domain\Events\Enums',
        'App\Domain\Cfp\Enums',
        'App\Domain\Directory\Enums',
        'App\Domain\Content\Enums',
        // tightened in architecture step 3b: the preset also hardcodes
        // App\Mail, which the modular layout replaces with per-context mail.
        'App\Domain\Identity\Mail',
        // tightened in architecture step 3a: bounded-context controllers and
        // form requests live under App\Application, not App\Http.
        'App\Application\Events\Http',
        'App\Application\Cfp\Http',
        'App\Application\Identity\Http',
        'App\Application\Directory\Http',
        'App\Application\Content\Http',
        'App\Application\Shared\Http',
    ]);

arch()->preset()->security();

arch('enums are backed and string')
    ->expect([
        'App\Domain\Identity\Enums',
        'App\Domain\Events\Enums',
        'App\Domain\Cfp\Enums',
        'App\Domain\Directory\Enums',
        'App\Domain\Content\Enums',
    ])
    ->toBeEnums()
    ->toBeStringBackedEnums();

arch('models')
    ->expect([
        'App\Domain\Identity\Models',
        'App\Domain\Events\Models',
        'App\Domain\Cfp\Models',
        'App\Domain\Directory\Models',
        'App\Domain\Content\Models',
    ])
    ->toBeClasses()
    ->toExtend(Model::class);

arch('policies')
    ->expect([
        'App\Domain\Events\Policies',
        'App\Domain\Cfp\Policies',
        'App\Domain\Directory\Policies',
        'App\Domain\Content\Policies',
    ])
    ->toHaveSuffix('Policy');

arch('form requests')
    ->expect([
        'App\Application\Events\Http\Requests',
        'App\Application\Cfp\Http\Requests',
        'App\Application\Identity\Http\Requests',
        'App\Application\Directory\Http\Requests',
        'App\Application\Content\Http\Requests',
    ])
    ->toExtend(FormRequest::class);

arch('actions are final and invokable')
    ->expect([
        'App\Domain\Events\Actions',
        'App\Domain\Cfp\Actions',
        'App\Domain\Directory\Actions',
        'App\Domain\Content\Actions',
    ])
    ->toBeFinal()
    ->toHaveMethod('__invoke');

arch('data objects are final and readonly')
    ->expect([
        'App\Domain\Events\Data',
        'App\Domain\Cfp\Data',
        'App\Domain\Identity\Data',
        'App\Domain\Directory\Data',
        'App\Domain\Content\Data',
        'App\Domain\Shared\Data',
    ])
    ->toBeFinal()
    ->toBeReadonly();

arch('query builders')
    ->expect([
        'App\Domain\Events\QueryBuilders',
        'App\Domain\Cfp\QueryBuilders',
        'App\Domain\Directory\QueryBuilders',
        'App\Domain\Content\QueryBuilders',
    ])
    ->toExtend(Builder::class);

arch('controllers are thin')
    ->expect([
        'App\Application\Events\Http\Controllers',
        'App\Application\Cfp\Http\Controllers',
        'App\Application\Identity\Http\Controllers',
        'App\Application\Directory\Http\Controllers',
        'App\Application\Content\Http\Controllers',
        'App\Application\Shared\Http\Controllers',
    ])
    ->toExtend(Controller::class)
    ->not->toUse([DB::class]);

arch('middleware')
    ->expect('App\Application\Shared\Http\Middleware')
    ->toHaveMethod('handle');

arch('domain is framework-http free')
    ->expect('App\Domain')
    ->not->toUse([
        'Illuminate\Http',
        'Inertia',
        'App\Http',
        'App\Application',
        'App\Infrastructure',
    ]);

arch('infrastructure implements domain contracts')
    ->expect([
        CloudflareImageStorage::class,
        DiskImageStorage::class,
    ])
    ->toImplement(ImageStorage::class);
