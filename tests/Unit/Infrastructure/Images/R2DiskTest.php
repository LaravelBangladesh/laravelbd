<?php

use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToWriteFile;

beforeEach(function () {
    config(['filesystems.disks.r2' => array_merge(config('filesystems.disks.r2'), [
        'key' => 'key',
        'secret' => 'secret',
        'bucket' => 'bucket',
        'endpoint' => 'http://127.0.0.1:1',
    ])]);
    Storage::forgetDisk('r2');
});

test('the r2 client gives up on a stalled connection', function () {
    $command = Storage::disk('r2')->getClient()->getCommand('PutObject', ['Bucket' => 'bucket', 'Key' => 'probe.txt']);

    expect($command['@http'])->toMatchArray(['connect_timeout' => 5, 'timeout' => 30]);
});

test('r2 write failures are reported instead of failing silently', function () {
    Exceptions::fake();

    expect(Storage::disk('r2')->put('probe.txt', 'probe'))->toBeFalse();

    Exceptions::assertReported(UnableToWriteFile::class);
});
