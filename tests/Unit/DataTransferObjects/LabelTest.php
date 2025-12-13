<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\Label;

it('can create label from array', function () {
    $label = Label::fromArray([
        'id' => 1,
        'name' => 'bug',
        'color' => 'ff0000',
        'description' => 'Something is broken',
    ]);

    expect($label->id)->toBe(1)
        ->and($label->name)->toBe('bug')
        ->and($label->color)->toBe('ff0000')
        ->and($label->description)->toBe('Something is broken');
});

it('can create label with null description', function () {
    $label = Label::fromArray([
        'id' => 1,
        'name' => 'enhancement',
        'color' => '00ff00',
    ]);

    expect($label->description)->toBeNull();
});

it('can convert label to array', function () {
    $label = Label::fromArray([
        'id' => 1,
        'name' => 'bug',
        'color' => 'ff0000',
        'description' => 'Something is broken',
    ]);

    $array = $label->toArray();

    expect($array)->toBeArray()
        ->and($array['name'])->toBe('bug');
});
