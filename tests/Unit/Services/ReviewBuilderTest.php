<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\DataTransferObjects\Review;
use ConduitUI\Pr\PullRequest;
use ConduitUI\Pr\Requests\CreatePullRequestReview;
use ConduitUI\Pr\Services\ReviewBuilder;
use Saloon\Http\Request;
use Saloon\Http\Response;

class MockReviewBuilderResponse extends Response
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

class ReviewBuilderTestConnector extends Connector
{
    public ?Request $lastRequest = null;

    public function __construct()
    {
        parent::__construct('test-token');
    }

    public function send(Request $request, ...$args): Response
    {
        $this->lastRequest = $request;

        return new MockReviewBuilderResponse([
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

function createReviewBuilderTestConnector(): ReviewBuilderTestConnector
{
    return new ReviewBuilderTestConnector;
}

function createReviewBuilderTestPullRequestData(): PullRequestData
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

it('can create ReviewBuilder from PullRequest', function () {
    $connector = createReviewBuilderTestConnector();
    $prData = createReviewBuilderTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $builder = $pr->review();

    expect($builder)->toBeInstanceOf(ReviewBuilder::class);
});

it('can approve with comment', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    $review = $builder->approve('LGTM!')->submit();

    expect($review)->toBeInstanceOf(Review::class)
        ->and($connector->lastRequest)->toBeInstanceOf(CreatePullRequestReview::class);

    $body = $connector->lastRequest->body()->all();
    expect($body['event'])->toBe('APPROVE')
        ->and($body['body'])->toBe('LGTM!');
});

it('can approve without comment', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    $review = $builder->approve()->submit();

    expect($review)->toBeInstanceOf(Review::class);

    $body = $connector->lastRequest->body()->all();
    expect($body['event'])->toBe('APPROVE')
        ->and($body)->not->toHaveKey('body');
});

it('can request changes with comment', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    $review = $builder->requestChanges('Please fix these issues')->submit();

    expect($review)->toBeInstanceOf(Review::class);

    $body = $connector->lastRequest->body()->all();
    expect($body['event'])->toBe('REQUEST_CHANGES')
        ->and($body['body'])->toBe('Please fix these issues');
});

it('can request changes without comment', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    $review = $builder->requestChanges()->submit();

    expect($review)->toBeInstanceOf(Review::class);

    $body = $connector->lastRequest->body()->all();
    expect($body['event'])->toBe('REQUEST_CHANGES')
        ->and($body['body'])->toBe('Changes requested');
});

it('can add comment review', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    $review = $builder->comment('Just a comment')->submit();

    expect($review)->toBeInstanceOf(Review::class);

    $body = $connector->lastRequest->body()->all();
    expect($body['event'])->toBe('COMMENT')
        ->and($body['body'])->toBe('Just a comment');
});

it('can add inline comment', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    $review = $builder
        ->approve('LGTM with minor suggestion')
        ->addInlineComment('src/File.php', 42, 'Consider refactoring this')
        ->submit();

    expect($review)->toBeInstanceOf(Review::class);

    $body = $connector->lastRequest->body()->all();
    expect($body['comments'])->toBeArray()
        ->and($body['comments'])->toHaveCount(1)
        ->and($body['comments'][0])->toMatchArray([
            'path' => 'src/File.php',
            'line' => 42,
            'body' => 'Consider refactoring this',
        ]);
});

it('can add multiple inline comments', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    $review = $builder
        ->requestChanges('Several issues found')
        ->addInlineComment('src/File.php', 10, 'Issue 1')
        ->addInlineComment('src/File.php', 20, 'Issue 2')
        ->addInlineComment('src/Other.php', 5, 'Issue 3')
        ->submit();

    expect($review)->toBeInstanceOf(Review::class);

    $body = $connector->lastRequest->body()->all();
    expect($body['comments'])->toHaveCount(3);
});

it('can add code suggestion', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    $review = $builder
        ->comment('Code improvement suggestion')
        ->addSuggestion('src/Controller.php', 20, 22, 'return $this->repository->findOrFail($id);')
        ->submit();

    expect($review)->toBeInstanceOf(Review::class);

    $body = $connector->lastRequest->body()->all();
    expect($body['comments'])->toBeArray()
        ->and($body['comments'])->toHaveCount(1)
        ->and($body['comments'][0]['path'])->toBe('src/Controller.php')
        ->and($body['comments'][0]['start_line'])->toBe(20)
        ->and($body['comments'][0]['line'])->toBe(22)
        ->and($body['comments'][0]['body'])->toContain('```suggestion');
});

it('can mix inline comments and suggestions', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    $review = $builder
        ->requestChanges('Mixed feedback')
        ->addInlineComment('src/File.php', 10, 'This is wrong')
        ->addSuggestion('src/File.php', 20, 22, 'better code here')
        ->addInlineComment('src/Other.php', 5, 'Also needs work')
        ->submit();

    expect($review)->toBeInstanceOf(Review::class);

    $body = $connector->lastRequest->body()->all();
    expect($body['comments'])->toHaveCount(3);
});

it('supports method chaining', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    $result = $builder
        ->approve('Great!')
        ->addInlineComment('src/File.php', 10, 'Nice work');

    expect($result)->toBe($builder);
});

it('throws exception when submitting without event', function () {
    $connector = createReviewBuilderTestConnector();
    $builder = new ReviewBuilder($connector, 'owner/repo', 123);

    expect(fn () => $builder->submit())
        ->toThrow(\InvalidArgumentException::class, 'Review event is required');
});
