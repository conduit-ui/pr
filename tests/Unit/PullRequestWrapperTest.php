<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\Comment;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\PullRequest;
use Saloon\Http\Request;
use Saloon\Http\Response;

class MockResponse extends Response
{
    public function __construct(private array $data)
    {
        // Skip parent constructor
    }

    public function json(string|int|null $key = null, mixed $default = null): mixed
    {
        if ($key !== null) {
            return $this->data[$key] ?? $default;
        }

        return $this->data;
    }
}

class TestConnector extends Connector
{
    private int $callIndex = 0;

    /**
     * @param  array<int, array<string, mixed>>  $responses
     */
    public function __construct(private array $responses = [])
    {
        parent::__construct('test-token');
    }

    public function send(Request $request, ...$args): Response
    {
        $responseData = $this->responses[$this->callIndex++] ?? [];

        return new MockResponse($responseData);
    }
}

function createMockConnector(array $responses = []): Connector
{
    return new TestConnector($responses);
}

function createTestPullRequestData(): PullRequestData
{
    return PullRequestData::fromArray([
        'number' => 123,
        'title' => 'Test PR',
        'body' => 'Test description',
        'state' => 'open',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'html_url' => 'https://github.com/owner/repo/pull/123',
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-01T00:00:00Z',
        'draft' => false,
        'head' => [
            'ref' => 'feature-branch',
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
        ],
        'base' => [
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
        ],
    ]);
}

it('can get commits from pull request', function () {
    $mockCommits = [
        [
            'sha' => 'commit1',
            'commit' => [
                'message' => 'First commit',
            ],
        ],
        [
            'sha' => 'commit2',
            'commit' => [
                'message' => 'Second commit',
            ],
        ],
    ];

    $connector = createMockConnector([$mockCommits]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $commits = $pr->commits();

    expect($commits)->toBeArray()
        ->and($commits)->toHaveCount(2)
        ->and($commits[0]['sha'])->toBe('commit1')
        ->and($commits[1]['sha'])->toBe('commit2');
});

it('returns empty array when no commits', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $commits = $pr->commits();

    expect($commits)->toBeArray()
        ->and($commits)->toBeEmpty();
});

it('can get issue comments from pull request', function () {
    $mockComments = [
        [
            'id' => 1,
            'user' => [
                'id' => 1,
                'login' => 'commenter1',
                'avatar_url' => 'https://example.com/avatar1.jpg',
                'html_url' => 'https://github.com/commenter1',
                'type' => 'User',
            ],
            'body' => 'First comment',
            'html_url' => 'https://github.com/owner/repo/issues/123#issuecomment-1',
            'created_at' => '2025-01-01T10:00:00Z',
            'updated_at' => '2025-01-01T10:00:00Z',
        ],
        [
            'id' => 2,
            'user' => [
                'id' => 2,
                'login' => 'commenter2',
                'avatar_url' => 'https://example.com/avatar2.jpg',
                'html_url' => 'https://github.com/commenter2',
                'type' => 'User',
            ],
            'body' => 'Second comment',
            'html_url' => 'https://github.com/owner/repo/issues/123#issuecomment-2',
            'created_at' => '2025-01-01T11:00:00Z',
            'updated_at' => '2025-01-01T11:00:00Z',
        ],
    ];

    $connector = createMockConnector([$mockComments]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $comments = $pr->issueComments();

    expect($comments)->toBeArray()
        ->and($comments)->toHaveCount(2)
        ->and($comments[0])->toBeInstanceOf(Comment::class)
        ->and($comments[0]->id)->toBe(1)
        ->and($comments[0]->body)->toBe('First comment')
        ->and($comments[1])->toBeInstanceOf(Comment::class)
        ->and($comments[1]->id)->toBe(2)
        ->and($comments[1]->body)->toBe('Second comment');
});

it('returns empty array when no issue comments', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $comments = $pr->issueComments();

    expect($comments)->toBeArray()
        ->and($comments)->toBeEmpty();
});
