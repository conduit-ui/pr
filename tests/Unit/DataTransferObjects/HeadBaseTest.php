<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\Base;
use ConduitUI\Pr\DataTransferObjects\Head;

it('can create head from array', function () {
    $head = Head::fromArray([
        'ref' => 'feature-branch',
        'sha' => 'abc123def456',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'repo' => [
            'id' => 1,
            'name' => 'repo',
            'full_name' => 'owner/repo',
            'html_url' => 'https://github.com/owner/repo',
            'private' => false,
        ],
    ]);

    expect($head->ref)->toBe('feature-branch')
        ->and($head->sha)->toBe('abc123def456')
        ->and($head->user->login)->toBe('testuser')
        ->and($head->repo->fullName)->toBe('owner/repo');
});

it('can create base from array', function () {
    $base = Base::fromArray([
        'ref' => 'main',
        'sha' => 'def456abc123',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'repo' => [
            'id' => 1,
            'name' => 'repo',
            'full_name' => 'owner/repo',
            'html_url' => 'https://github.com/owner/repo',
            'private' => false,
        ],
    ]);

    expect($base->ref)->toBe('main')
        ->and($base->sha)->toBe('def456abc123');
});

it('can convert head to array', function () {
    $head = Head::fromArray([
        'ref' => 'feature',
        'sha' => 'abc123',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'repo' => [
            'id' => 1,
            'name' => 'repo',
            'full_name' => 'owner/repo',
            'html_url' => 'https://github.com/owner/repo',
            'private' => false,
        ],
    ]);

    $array = $head->toArray();

    expect($array)->toBeArray()
        ->and($array['ref'])->toBe('feature');
});

it('can convert base to array', function () {
    $base = Base::fromArray([
        'ref' => 'main',
        'sha' => 'def456',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'repo' => [
            'id' => 1,
            'name' => 'repo',
            'full_name' => 'owner/repo',
            'html_url' => 'https://github.com/owner/repo',
            'private' => false,
        ],
    ]);

    $array = $base->toArray();

    expect($array)->toBeArray()
        ->and($array['ref'])->toBe('main');
});
