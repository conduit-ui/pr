<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\CheckSummary;

it('can create CheckSummary from array', function () {
    $data = [
        'total' => 10,
        'passing' => 8,
        'failing' => 1,
        'pending' => 1,
        'neutral' => 0,
        'skipped' => 0,
    ];

    $summary = CheckSummary::fromArray($data);

    expect($summary->total)->toBe(10)
        ->and($summary->passing)->toBe(8)
        ->and($summary->failing)->toBe(1)
        ->and($summary->pending)->toBe(1)
        ->and($summary->neutral)->toBe(0)
        ->and($summary->skipped)->toBe(0);
});

it('can check if all checks are passing', function () {
    $passingData = [
        'total' => 5,
        'passing' => 5,
        'failing' => 0,
        'pending' => 0,
        'neutral' => 0,
        'skipped' => 0,
    ];

    $summary = CheckSummary::fromArray($passingData);
    expect($summary->allPassing())->toBeTrue();

    $failingData = [
        'total' => 5,
        'passing' => 4,
        'failing' => 1,
        'pending' => 0,
        'neutral' => 0,
        'skipped' => 0,
    ];

    $summary = CheckSummary::fromArray($failingData);
    expect($summary->allPassing())->toBeFalse();
});

it('can check if has failures', function () {
    $data = [
        'total' => 5,
        'passing' => 4,
        'failing' => 1,
        'pending' => 0,
        'neutral' => 0,
        'skipped' => 0,
    ];

    $summary = CheckSummary::fromArray($data);
    expect($summary->hasFailures())->toBeTrue();
});

it('can check if has pending', function () {
    $data = [
        'total' => 5,
        'passing' => 4,
        'failing' => 0,
        'pending' => 1,
        'neutral' => 0,
        'skipped' => 0,
    ];

    $summary = CheckSummary::fromArray($data);
    expect($summary->hasPending())->toBeTrue();
});

it('can serialize to array', function () {
    $data = [
        'total' => 10,
        'passing' => 8,
        'failing' => 1,
        'pending' => 1,
        'neutral' => 0,
        'skipped' => 0,
    ];

    $summary = CheckSummary::fromArray($data);
    $array = $summary->toArray();

    expect($array)->toBe($data);
});
