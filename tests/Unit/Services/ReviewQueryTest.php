<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\Review;
use ConduitUI\Pr\Services\ReviewQuery;
use Saloon\Http\Request;
use Saloon\Http\Response;

class MockReviewQueryResponse extends Response
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

class ReviewQueryTestConnector extends Connector
{
    public ?Request $lastRequest = null;

    public array $mockReviews = [];

    public function __construct()
    {
        parent::__construct('test-token');

        // Default mock reviews
        $this->mockReviews = [
            [
                'id' => 1,
                'user' => [
                    'id' => 1,
                    'login' => 'reviewer1',
                    'avatar_url' => 'https://example.com/avatar1.jpg',
                    'html_url' => 'https://github.com/reviewer1',
                    'type' => 'User',
                ],
                'state' => 'APPROVED',
                'body' => 'LGTM',
                'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-1',
                'submitted_at' => '2025-01-01T10:00:00Z',
            ],
            [
                'id' => 2,
                'user' => [
                    'id' => 2,
                    'login' => 'reviewer2',
                    'avatar_url' => 'https://example.com/avatar2.jpg',
                    'html_url' => 'https://github.com/reviewer2',
                    'type' => 'User',
                ],
                'state' => 'CHANGES_REQUESTED',
                'body' => 'Please fix',
                'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-2',
                'submitted_at' => '2025-01-01T11:00:00Z',
            ],
            [
                'id' => 3,
                'user' => [
                    'id' => 3,
                    'login' => 'reviewer3',
                    'avatar_url' => 'https://example.com/avatar3.jpg',
                    'html_url' => 'https://github.com/reviewer3',
                    'type' => 'User',
                ],
                'state' => 'COMMENTED',
                'body' => 'Just a comment',
                'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-3',
                'submitted_at' => '2025-01-01T12:00:00Z',
            ],
            [
                'id' => 4,
                'user' => [
                    'id' => 1,
                    'login' => 'reviewer1',
                    'avatar_url' => 'https://example.com/avatar1.jpg',
                    'html_url' => 'https://github.com/reviewer1',
                    'type' => 'User',
                ],
                'state' => 'APPROVED',
                'body' => 'Still looks good',
                'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-4',
                'submitted_at' => '2025-01-01T13:00:00Z',
            ],
        ];
    }

    public function send(Request $request, ...$args): Response
    {
        $this->lastRequest = $request;

        return new MockReviewQueryResponse($this->mockReviews);
    }
}

function createReviewQueryTestConnector(): ReviewQueryTestConnector
{
    return new ReviewQueryTestConnector;
}

it('can get all reviews', function () {
    $connector = createReviewQueryTestConnector();
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $reviews = $query->get();

    expect($reviews)->toBeArray()
        ->and($reviews)->toHaveCount(4)
        ->and($reviews[0])->toBeInstanceOf(Review::class);
});

it('can filter approved reviews', function () {
    $connector = createReviewQueryTestConnector();
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $approved = $query->whereApproved();

    expect($approved)->toBeArray()
        ->and($approved)->toHaveCount(2)
        ->and($approved[0]->state)->toBe('APPROVED');
});

it('can filter changes requested reviews', function () {
    $connector = createReviewQueryTestConnector();
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $changesRequested = $query->whereChangesRequested();

    expect($changesRequested)->toBeArray()
        ->and($changesRequested)->toHaveCount(1)
        ->and($changesRequested[0]->state)->toBe('CHANGES_REQUESTED');
});

it('can filter commented reviews', function () {
    $connector = createReviewQueryTestConnector();
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $commented = $query->whereCommented();

    expect($commented)->toBeArray()
        ->and($commented)->toHaveCount(1)
        ->and($commented[0]->state)->toBe('COMMENTED');
});

it('can filter reviews by user', function () {
    $connector = createReviewQueryTestConnector();
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $userReviews = $query->byUser('reviewer1');

    expect($userReviews)->toBeArray()
        ->and($userReviews)->toHaveCount(2)
        ->and($userReviews[0]->user->login)->toBe('reviewer1');
});

it('can get latest review', function () {
    $connector = createReviewQueryTestConnector();
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $latest = $query->latest();

    expect($latest)->toBeInstanceOf(Review::class)
        ->and($latest->id)->toBe(4);
});

it('can get first review', function () {
    $connector = createReviewQueryTestConnector();
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $first = $query->first();

    expect($first)->toBeInstanceOf(Review::class)
        ->and($first->id)->toBe(1);
});

it('can count reviews', function () {
    $connector = createReviewQueryTestConnector();
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $count = $query->count();

    expect($count)->toBe(4);
});

it('returns null for latest when no reviews', function () {
    $connector = createReviewQueryTestConnector();
    $connector->mockReviews = [];
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $latest = $query->latest();

    expect($latest)->toBeNull();
});

it('returns null for first when no reviews', function () {
    $connector = createReviewQueryTestConnector();
    $connector->mockReviews = [];
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $first = $query->first();

    expect($first)->toBeNull();
});

it('supports method chaining for multiple filters', function () {
    $connector = createReviewQueryTestConnector();
    $query = new ReviewQuery($connector, 'owner/repo', 123);

    $filtered = $query->whereApproved();
    $byUser = array_values(array_filter($filtered, fn ($review) => $review->user->login === 'reviewer1'));

    expect($byUser)->toHaveCount(2);
});
