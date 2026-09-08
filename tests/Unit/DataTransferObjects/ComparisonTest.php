<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\Comparison;

it('can create comparison from array', function () {
    $comparison = Comparison::fromArray([
        'ahead_by' => 1,
        'behind_by' => 3,
        'status' => 'diverged',
    ]);

    expect($comparison)
        ->aheadBy->toBe(1)
        ->behindBy->toBe(3)
        ->status->toBe('diverged');
});

it('can serialize comparison to array', function () {
    $comparison = new Comparison(2, 0, 'ahead');

    expect($comparison->toArray())
        ->toBe([
            'ahead_by' => 2,
            'behind_by' => 0,
            'status' => 'ahead',
        ]);
});

it('handles zero values correctly', function () {
    $comparison = Comparison::fromArray([
        'ahead_by' => 0,
        'behind_by' => 0,
        'status' => 'identical',
    ]);

    expect($comparison)
        ->aheadBy->toBe(0)
        ->behindBy->toBe(0)
        ->status->toBe('identical');
});

it('can check comparison status', function (string $status) {
    $comparison = new Comparison(1, 1, $status);

    expect($comparison->status)->toBe($status);
})->with([
    'ahead' => 'ahead',
    'behind' => 'behind',
    'diverged' => 'diverged',
    'identical' => 'identical',
]);
