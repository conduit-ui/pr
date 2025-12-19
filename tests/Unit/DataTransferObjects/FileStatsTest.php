<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\FileStats;

it('can create FileStats from array', function () {
    $data = [
        'total' => 10,
        'added' => 3,
        'modified' => 5,
        'removed' => 2,
        'renamed' => 0,
        'total_additions' => 150,
        'total_deletions' => 75,
        'total_changes' => 225,
    ];

    $stats = FileStats::fromArray($data);

    expect($stats->total)->toBe(10)
        ->and($stats->added)->toBe(3)
        ->and($stats->modified)->toBe(5)
        ->and($stats->removed)->toBe(2)
        ->and($stats->renamed)->toBe(0)
        ->and($stats->totalAdditions)->toBe(150)
        ->and($stats->totalDeletions)->toBe(75)
        ->and($stats->totalChanges)->toBe(225);
});

it('can serialize to array', function () {
    $data = [
        'total' => 10,
        'added' => 3,
        'modified' => 5,
        'removed' => 2,
        'renamed' => 0,
        'total_additions' => 150,
        'total_deletions' => 75,
        'total_changes' => 225,
    ];

    $stats = FileStats::fromArray($data);
    $array = $stats->toArray();

    expect($array)->toBe($data);
});

it('handles zero values correctly', function () {
    $data = [
        'total' => 0,
        'added' => 0,
        'modified' => 0,
        'removed' => 0,
        'renamed' => 0,
        'total_additions' => 0,
        'total_deletions' => 0,
        'total_changes' => 0,
    ];

    $stats = FileStats::fromArray($data);

    expect($stats->total)->toBe(0)
        ->and($stats->added)->toBe(0)
        ->and($stats->totalChanges)->toBe(0);
});
