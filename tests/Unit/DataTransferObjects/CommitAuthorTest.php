<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\CommitAuthor;

it('can create commit author from array', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'date' => '2025-01-01T10:00:00Z',
    ];

    $author = CommitAuthor::fromArray($data);

    expect($author->name)->toBe('John Doe')
        ->and($author->email)->toBe('john@example.com')
        ->and($author->date)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($author->date->format('Y-m-d'))->toBe('2025-01-01');
});

it('can convert commit author to array', function () {
    $data = [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'date' => '2025-01-15T14:30:00Z',
    ];

    $author = CommitAuthor::fromArray($data);
    $result = $author->toArray();

    expect($result['name'])->toBe('Jane Smith')
        ->and($result['email'])->toBe('jane@example.com')
        ->and($result['date'])->toBe('2025-01-15T14:30:00+00:00');
});
