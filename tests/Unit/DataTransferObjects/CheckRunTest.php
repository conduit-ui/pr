<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\CheckRun;

it('can create check run from array', function () {
    $checkRun = CheckRun::fromArray([
        'id' => 1,
        'name' => 'PHPStan',
        'status' => 'completed',
        'conclusion' => 'success',
        'html_url' => 'https://github.com/owner/repo/runs/1',
        'started_at' => '2025-01-01T10:00:00Z',
        'completed_at' => '2025-01-01T10:05:00Z',
    ]);

    expect($checkRun->id)->toBe(1)
        ->and($checkRun->name)->toBe('PHPStan')
        ->and($checkRun->status)->toBe('completed')
        ->and($checkRun->conclusion)->toBe('success');
});

it('can create check run with null conclusion', function () {
    $checkRun = CheckRun::fromArray([
        'id' => 1,
        'name' => 'Tests',
        'status' => 'in_progress',
        'conclusion' => null,
        'html_url' => 'https://github.com/owner/repo/runs/1',
        'started_at' => '2025-01-01T10:00:00Z',
    ]);

    expect($checkRun->conclusion)->toBeNull()
        ->and($checkRun->completedAt)->toBeNull();
});

it('can check if check run is completed', function () {
    $checkRun = CheckRun::fromArray([
        'id' => 1,
        'name' => 'Tests',
        'status' => 'completed',
        'conclusion' => 'success',
        'html_url' => 'https://github.com/owner/repo/runs/1',
        'started_at' => '2025-01-01T10:00:00Z',
        'completed_at' => '2025-01-01T10:05:00Z',
    ]);

    expect($checkRun->isCompleted())->toBeTrue();
});

it('can check if check run is successful', function () {
    $checkRun = CheckRun::fromArray([
        'id' => 1,
        'name' => 'Tests',
        'status' => 'completed',
        'conclusion' => 'success',
        'html_url' => 'https://github.com/owner/repo/runs/1',
        'started_at' => '2025-01-01T10:00:00Z',
        'completed_at' => '2025-01-01T10:05:00Z',
    ]);

    expect($checkRun->isSuccessful())->toBeTrue();
});

it('can check if check run failed', function () {
    $checkRun = CheckRun::fromArray([
        'id' => 1,
        'name' => 'Tests',
        'status' => 'completed',
        'conclusion' => 'failure',
        'html_url' => 'https://github.com/owner/repo/runs/1',
        'started_at' => '2025-01-01T10:00:00Z',
        'completed_at' => '2025-01-01T10:05:00Z',
    ]);

    expect($checkRun->isFailed())->toBeTrue()
        ->and($checkRun->isSuccessful())->toBeFalse();
});

it('can check if check run timed out', function () {
    $checkRun = CheckRun::fromArray([
        'id' => 1,
        'name' => 'Tests',
        'status' => 'completed',
        'conclusion' => 'timed_out',
        'html_url' => 'https://github.com/owner/repo/runs/1',
        'started_at' => '2025-01-01T10:00:00Z',
        'completed_at' => '2025-01-01T10:05:00Z',
    ]);

    expect($checkRun->isFailed())->toBeTrue();
});

it('can check if check run action required', function () {
    $checkRun = CheckRun::fromArray([
        'id' => 1,
        'name' => 'Tests',
        'status' => 'completed',
        'conclusion' => 'action_required',
        'html_url' => 'https://github.com/owner/repo/runs/1',
        'started_at' => '2025-01-01T10:00:00Z',
        'completed_at' => '2025-01-01T10:05:00Z',
    ]);

    expect($checkRun->isFailed())->toBeTrue();
});

it('can convert check run to array', function () {
    $checkRun = CheckRun::fromArray([
        'id' => 1,
        'name' => 'PHPStan',
        'status' => 'completed',
        'conclusion' => 'success',
        'html_url' => 'https://github.com/owner/repo/runs/1',
        'started_at' => '2025-01-01T10:00:00Z',
        'completed_at' => '2025-01-01T10:05:00Z',
    ]);

    $array = $checkRun->toArray();

    expect($array)->toBeArray()
        ->and($array['name'])->toBe('PHPStan');
});
