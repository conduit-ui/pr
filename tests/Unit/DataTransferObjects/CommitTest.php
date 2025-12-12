<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\Commit;
use ConduitUI\Pr\DataTransferObjects\CommitAuthor;
use ConduitUI\Pr\DataTransferObjects\User;

it('can create commit from array with github users', function () {
    $data = [
        'sha' => 'abc123def456',
        'commit' => [
            'message' => 'Initial commit',
            'author' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'date' => '2025-01-01T10:00:00Z',
            ],
            'committer' => [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'date' => '2025-01-01T10:05:00Z',
            ],
        ],
        'html_url' => 'https://github.com/owner/repo/commit/abc123def456',
        'author' => [
            'id' => 1,
            'login' => 'johndoe',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/johndoe',
            'type' => 'User',
        ],
        'committer' => [
            'id' => 2,
            'login' => 'janesmith',
            'avatar_url' => 'https://example.com/avatar2.jpg',
            'html_url' => 'https://github.com/janesmith',
            'type' => 'User',
        ],
    ];

    $commit = Commit::fromArray($data);

    expect($commit->sha)->toBe('abc123def456')
        ->and($commit->message)->toBe('Initial commit')
        ->and($commit->author)->toBeInstanceOf(CommitAuthor::class)
        ->and($commit->author->name)->toBe('John Doe')
        ->and($commit->author->email)->toBe('john@example.com')
        ->and($commit->committer)->toBeInstanceOf(CommitAuthor::class)
        ->and($commit->committer->name)->toBe('Jane Smith')
        ->and($commit->htmlUrl)->toBe('https://github.com/owner/repo/commit/abc123def456')
        ->and($commit->githubAuthor)->toBeInstanceOf(User::class)
        ->and($commit->githubAuthor->login)->toBe('johndoe')
        ->and($commit->githubCommitter)->toBeInstanceOf(User::class)
        ->and($commit->githubCommitter->login)->toBe('janesmith');
});

it('can create commit without github users', function () {
    $data = [
        'sha' => 'def456abc789',
        'commit' => [
            'message' => 'Fix bug',
            'author' => [
                'name' => 'External Author',
                'email' => 'external@example.com',
                'date' => '2025-01-02T11:00:00Z',
            ],
            'committer' => [
                'name' => 'External Committer',
                'email' => 'committer@example.com',
                'date' => '2025-01-02T11:05:00Z',
            ],
        ],
        'html_url' => 'https://github.com/owner/repo/commit/def456abc789',
    ];

    $commit = Commit::fromArray($data);

    expect($commit->sha)->toBe('def456abc789')
        ->and($commit->message)->toBe('Fix bug')
        ->and($commit->githubAuthor)->toBeNull()
        ->and($commit->githubCommitter)->toBeNull();
});

it('can convert commit to array', function () {
    $data = [
        'sha' => 'abc123',
        'commit' => [
            'message' => 'Test commit',
            'author' => [
                'name' => 'Test Author',
                'email' => 'test@example.com',
                'date' => '2025-01-01T12:00:00Z',
            ],
            'committer' => [
                'name' => 'Test Committer',
                'email' => 'committer@example.com',
                'date' => '2025-01-01T12:05:00Z',
            ],
        ],
        'html_url' => 'https://github.com/owner/repo/commit/abc123',
        'author' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'committer' => [
            'id' => 2,
            'login' => 'testcommitter',
            'avatar_url' => 'https://example.com/avatar2.jpg',
            'html_url' => 'https://github.com/testcommitter',
            'type' => 'User',
        ],
    ];

    $commit = Commit::fromArray($data);
    $result = $commit->toArray();

    expect($result['sha'])->toBe('abc123')
        ->and($result['commit']['message'])->toBe('Test commit')
        ->and($result['commit']['author']['name'])->toBe('Test Author')
        ->and($result['commit']['committer']['name'])->toBe('Test Committer')
        ->and($result['html_url'])->toBe('https://github.com/owner/repo/commit/abc123')
        ->and($result['author']['login'])->toBe('testuser')
        ->and($result['committer']['login'])->toBe('testcommitter');
});
