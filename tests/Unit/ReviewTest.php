<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\PullRequest;
use ConduitUI\Pr\Requests\CreatePullRequestReview;
use Saloon\Http\Request;
use Saloon\Http\Response;

class MockReviewResponse extends Response
{
    public function __construct(private array $data = [])
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

class ReviewTestConnector extends Connector
{
    public ?Request $lastRequest = null;

    public function __construct()
    {
        parent::__construct('test-token');
    }

    public function send(Request $request, ...$args): Response
    {
        $this->lastRequest = $request;

        return new MockReviewResponse([
            'id' => 1,
            'user' => [
                'id' => 1,
                'login' => 'reviewer',
                'avatar_url' => 'https://example.com/avatar.jpg',
                'html_url' => 'https://github.com/reviewer',
                'type' => 'User',
            ],
            'state' => 'APPROVED',
            'body' => 'LGTM',
            'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-1',
            'submitted_at' => '2025-01-01T10:00:00Z',
        ]);
    }
}

function createReviewTestConnector(): ReviewTestConnector
{
    return new ReviewTestConnector;
}

function createReviewTestPullRequestData(): PullRequestData
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

it('can submit review without comments', function () {
    $connector = createReviewTestConnector();
    $prData = createReviewTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $pr->submitReview('APPROVE', 'Looks good!');

    expect($connector->lastRequest)->toBeInstanceOf(CreatePullRequestReview::class);

    $body = $connector->lastRequest->body()->all();

    expect($body)->toHaveKey('event')
        ->and($body['event'])->toBe('APPROVE')
        ->and($body)->toHaveKey('body')
        ->and($body['body'])->toBe('Looks good!')
        ->and($body)->not->toHaveKey('comments');
});

it('can submit review with inline comments', function () {
    $connector = createReviewTestConnector();
    $prData = createReviewTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $comments = [
        ['path' => 'src/File.php', 'line' => 42, 'body' => 'Consider refactoring this'],
        ['path' => 'src/Other.php', 'line' => 10, 'body' => 'Nice improvement!'],
    ];

    $pr->submitReview('COMMENT', 'Review with inline comments', $comments);

    expect($connector->lastRequest)->toBeInstanceOf(CreatePullRequestReview::class);

    $body = $connector->lastRequest->body()->all();

    expect($body)->toHaveKey('event')
        ->and($body['event'])->toBe('COMMENT')
        ->and($body)->toHaveKey('body')
        ->and($body['body'])->toBe('Review with inline comments')
        ->and($body)->toHaveKey('comments')
        ->and($body['comments'])->toBeArray()
        ->and($body['comments'])->toHaveCount(2)
        ->and($body['comments'][0])->toMatchArray([
            'path' => 'src/File.php',
            'line' => 42,
            'body' => 'Consider refactoring this',
        ])
        ->and($body['comments'][1])->toMatchArray([
            'path' => 'src/Other.php',
            'line' => 10,
            'body' => 'Nice improvement!',
        ]);
});

it('can submit review with only inline comments', function () {
    $connector = createReviewTestConnector();
    $prData = createReviewTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $comments = [
        ['path' => 'README.md', 'line' => 5, 'body' => 'Typo here'],
    ];

    $pr->submitReview('COMMENT', null, $comments);

    expect($connector->lastRequest)->toBeInstanceOf(CreatePullRequestReview::class);

    $body = $connector->lastRequest->body()->all();

    expect($body)->toHaveKey('event')
        ->and($body['event'])->toBe('COMMENT')
        ->and($body)->not->toHaveKey('body')
        ->and($body)->toHaveKey('comments')
        ->and($body['comments'])->toHaveCount(1);
});

it('approve method returns ReviewBuilder', function () {
    $connector = createReviewTestConnector();
    $prData = createReviewTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $pr->approve('LGTM')->submit();

    expect($connector->lastRequest)->toBeInstanceOf(CreatePullRequestReview::class);

    $body = $connector->lastRequest->body()->all();

    expect($body['event'])->toBe('APPROVE')
        ->and($body['body'])->toBe('LGTM')
        ->and($body)->not->toHaveKey('comments');
});

it('requestChanges method returns ReviewBuilder', function () {
    $connector = createReviewTestConnector();
    $prData = createReviewTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $pr->requestChanges('Please fix these issues')->submit();

    expect($connector->lastRequest)->toBeInstanceOf(CreatePullRequestReview::class);

    $body = $connector->lastRequest->body()->all();

    expect($body['event'])->toBe('REQUEST_CHANGES')
        ->and($body['body'])->toBe('Please fix these issues')
        ->and($body)->not->toHaveKey('comments');
});

it('can request changes with inline comments using submitReview', function () {
    $connector = createReviewTestConnector();
    $prData = createReviewTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $comments = [
        ['path' => 'src/Security.php', 'line' => 15, 'body' => 'This has a security vulnerability'],
        ['path' => 'src/Security.php', 'line' => 20, 'body' => 'Missing input validation'],
    ];

    $pr->submitReview('REQUEST_CHANGES', 'Security issues found', $comments);

    expect($connector->lastRequest)->toBeInstanceOf(CreatePullRequestReview::class);

    $body = $connector->lastRequest->body()->all();

    expect($body['event'])->toBe('REQUEST_CHANGES')
        ->and($body['body'])->toBe('Security issues found')
        ->and($body['comments'])->toHaveCount(2);
});

it('can approve with inline comments using submitReview', function () {
    $connector = createReviewTestConnector();
    $prData = createReviewTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $comments = [
        ['path' => 'src/Feature.php', 'line' => 30, 'body' => 'Great implementation!'],
    ];

    $pr->submitReview('APPROVE', 'Looks good with minor suggestions', $comments);

    expect($connector->lastRequest)->toBeInstanceOf(CreatePullRequestReview::class);

    $body = $connector->lastRequest->body()->all();

    expect($body['event'])->toBe('APPROVE')
        ->and($body['body'])->toBe('Looks good with minor suggestions')
        ->and($body['comments'])->toHaveCount(1);
});

it('submitReview returns self for method chaining', function () {
    $connector = createReviewTestConnector();
    $prData = createReviewTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->submitReview('APPROVE', 'LGTM');

    expect($result)->toBe($pr);
});
