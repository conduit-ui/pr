<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\User;

it('can create user from array', function () {
    $user = User::fromArray([
        'id' => 1,
        'login' => 'testuser',
        'avatar_url' => 'https://example.com/avatar.jpg',
        'html_url' => 'https://github.com/testuser',
        'type' => 'User',
    ]);

    expect($user->id)->toBe(1)
        ->and($user->login)->toBe('testuser')
        ->and($user->avatarUrl)->toBe('https://example.com/avatar.jpg')
        ->and($user->htmlUrl)->toBe('https://github.com/testuser')
        ->and($user->type)->toBe('User');
});

it('can convert user to array', function () {
    $user = User::fromArray([
        'id' => 1,
        'login' => 'testuser',
        'avatar_url' => 'https://example.com/avatar.jpg',
        'html_url' => 'https://github.com/testuser',
        'type' => 'User',
    ]);

    $array = $user->toArray();

    expect($array)->toBeArray()
        ->and($array['id'])->toBe(1)
        ->and($array['login'])->toBe('testuser');
});
