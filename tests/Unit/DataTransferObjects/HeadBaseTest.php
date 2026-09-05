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

it('can create head with null user', function () {
    $head = Head::fromArray([
        'ref' => 'feature-branch',
        'sha' => 'abc123def456',
        'user' => null,
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
        ->and($head->user)->toBeNull()
        ->and($head->repo)->not()->toBeNull();
});

it('can create head with null repo', function () {
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
        'repo' => null,
    ]);

    expect($head->ref)->toBe('feature-branch')
        ->and($head->sha)->toBe('abc123def456')
        ->and($head->user)->not()->toBeNull()
        ->and($head->repo)->toBeNull();
});

it('can create base with null user', function () {
    $base = Base::fromArray([
        'ref' => 'main',
        'sha' => 'def456abc123',
        'user' => null,
        'repo' => [
            'id' => 1,
            'name' => 'repo',
            'full_name' => 'owner/repo',
            'html_url' => 'https://github.com/owner/repo',
            'private' => false,
        ],
    ]);

    expect($base->ref)->toBe('main')
        ->and($base->sha)->toBe('def456abc123')
        ->and($base->user)->toBeNull()
        ->and($base->repo)->not()->toBeNull();
});

it('can create base with null repo', function () {
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
        'repo' => null,
    ]);

    expect($base->ref)->toBe('main')
        ->and($base->sha)->toBe('def456abc123')
        ->and($base->user)->not()->toBeNull()
        ->and($base->repo)->toBeNull();
});

it('handles missing user and repo in head', function () {
    $head = Head::fromArray([
        'ref' => 'feature-branch',
        'sha' => 'abc123def456',
    ]);

    expect($head->ref)->toBe('feature-branch')
        ->and($head->sha)->toBe('abc123def456')
        ->and($head->user)->toBeNull()
        ->and($head->repo)->toBeNull();
});

it('handles missing user and repo in base', function () {
    $base = Base::fromArray([
        'ref' => 'main',
        'sha' => 'def456abc123',
    ]);

    expect($base->ref)->toBe('main')
        ->and($base->sha)->toBe('def456abc123')
        ->and($base->user)->toBeNull()
        ->and($base->repo)->toBeNull();
});

it('converts head with null properties to array', function () {
    $head = Head::fromArray([
        'ref' => 'feature-branch',
        'sha' => 'abc123def456',
        'user' => null,
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
        ->and($array['ref'])->toBe('feature-branch')
        ->and($array['user'])->toBeNull()
        ->and($array['repo'])->not()->toBeNull();
});

it('converts base with null properties to array', function () {
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
        'repo' => null,
    ]);

    $array = $base->toArray();

    expect($array)->toBeArray()
        ->and($array['ref'])->toBe('main')
        ->and($array['user'])->not()->toBeNull()
        ->and($array['repo'])->toBeNull();
});
