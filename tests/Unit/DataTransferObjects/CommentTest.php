<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\Comment;

it('can create comment from array', function () {
    $comment = Comment::fromArray([
        'id' => 1,
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'body' => 'This is a comment',
        'html_url' => 'https://github.com/owner/repo/issues/1#issuecomment-1',
        'created_at' => '2025-01-01T10:00:00Z',
        'updated_at' => '2025-01-01T11:00:00Z',
    ]);

    expect($comment->id)->toBe(1)
        ->and($comment->body)->toBe('This is a comment')
        ->and($comment->user->login)->toBe('testuser');
});

it('can convert comment to array', function () {
    $comment = Comment::fromArray([
        'id' => 1,
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'body' => 'Test comment',
        'html_url' => 'https://github.com/owner/repo/issues/1#issuecomment-1',
        'created_at' => '2025-01-01T10:00:00Z',
        'updated_at' => '2025-01-01T11:00:00Z',
    ]);

    $array = $comment->toArray();

    expect($array)->toBeArray()
        ->and($array['body'])->toBe('Test comment');
});
