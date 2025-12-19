<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\PullRequest;
use ConduitUI\Pr\QueryBuilder;
use Saloon\Http\Request;
use Saloon\Http\Response;

class QueryBuilderMockResponse extends Response
{
    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    public function __construct(private array $data)
    {
        // Skip parent constructor
    }

    public function json(string|int|null $key = null, mixed $default = null): mixed
    {
        return $this->data;
    }
}

class QueryBuilderTestConnector extends Connector
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $mockResponse = [];

    /**
     * @param  array<int, array<string, mixed>>  $response
     */
    public function __construct(array $response = [])
    {
        parent::__construct('test-token');
        $this->mockResponse = $response;
    }

    public function send(Request $request, ...$args): Response
    {
        return new QueryBuilderMockResponse($this->mockResponse);
    }
}

function createQueryBuilderConnector(array $response = []): Connector
{
    return new QueryBuilderTestConnector($response);
}

function createMockPrData(): array
{
    return [
        'number' => 1,
        'title' => 'Test PR',
        'body' => 'Test body',
        'state' => 'open',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'html_url' => 'https://github.com/owner/repo/pull/1',
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-01T00:00:00Z',
        'draft' => false,
        'head' => [
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
    ];
}

it('can set repository', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo');

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can filter by state', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->state('closed');

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can filter by open state', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->open();

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can filter by closed state', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->closed();

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can filter all states', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->all();

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can filter by author', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->author('testuser');

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can filter by label', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->label('bug');

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can set order by', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->orderBy('updated', 'asc');

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can set limit', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->take(10);

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can set page', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->page(2);

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can get pull requests', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $results = $builder->repository('owner/repo')->get();

    expect($results)->toBeArray()
        ->and($results)->toHaveCount(1)
        ->and($results[0])->toBeInstanceOf(PullRequest::class);
});

it('throws exception when repository not set', function () {
    $connector = createQueryBuilderConnector([]);
    $builder = new QueryBuilder($connector);

    $builder->get();
})->throws(InvalidArgumentException::class, 'Repository is required');

it('can get first pull request', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->first();

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('returns null when no pull requests found', function () {
    $connector = createQueryBuilderConnector([]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->first();

    expect($result)->toBeNull();
});

it('can count pull requests', function () {
    $connector = createQueryBuilderConnector([createMockPrData(), createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $count = $builder->repository('owner/repo')->count();

    expect($count)->toBe(2);
});

it('can chain multiple filters', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $results = $builder
        ->repository('owner/repo')
        ->open()
        ->author('testuser')
        ->label('bug')
        ->orderBy('updated', 'asc')
        ->take(10)
        ->page(1)
        ->get();

    expect($results)->toBeArray()
        ->and($results)->toHaveCount(1);
});

it('can filter by merged state', function () {
    $mergedPrData = createMockPrData();
    $mergedPrData['merged_at'] = '2025-01-02T00:00:00Z';

    $connector = createQueryBuilderConnector([$mergedPrData]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->whereMerged();

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can filter by draft state', function () {
    $draftPrData = createMockPrData();
    $draftPrData['draft'] = true;

    $connector = createQueryBuilderConnector([$draftPrData]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->whereDraft();

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can filter by base branch', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->whereBase('main');

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('can filter by head branch', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $result = $builder->repository('owner/repo')->whereHead('user:feature-branch');

    expect($result)->toBeInstanceOf(QueryBuilder::class);
});

it('implements PullRequestQueryInterface', function () {
    $connector = createQueryBuilderConnector([]);
    $builder = new QueryBuilder($connector);

    expect($builder)->toBeInstanceOf(\ConduitUI\Pr\Contracts\PullRequestQueryInterface::class);
});

it('can chain new filters with existing filters', function () {
    $connector = createQueryBuilderConnector([createMockPrData()]);
    $builder = new QueryBuilder($connector);

    $results = $builder
        ->repository('owner/repo')
        ->whereBase('main')
        ->whereHead('user:feature')
        ->whereDraft()
        ->author('testuser')
        ->get();

    expect($results)->toBeArray();
});

it('filters merged pull requests client-side', function () {
    $mergedPrData = createMockPrData();
    $mergedPrData['merged_at'] = '2025-01-02T00:00:00Z';

    $openPrData = createMockPrData();
    $openPrData['number'] = 2;

    $connector = createQueryBuilderConnector([$mergedPrData, $openPrData]);
    $builder = new QueryBuilder($connector);

    $results = $builder->repository('owner/repo')->all()->whereMerged()->get();

    expect($results)->toBeArray()
        ->and($results)->toHaveCount(1)
        ->and($results[0]->data->isMerged())->toBeTrue();
});

it('filters draft pull requests client-side', function () {
    $draftPrData = createMockPrData();
    $draftPrData['draft'] = true;

    $nonDraftPrData = createMockPrData();
    $nonDraftPrData['number'] = 2;
    $nonDraftPrData['draft'] = false;

    $connector = createQueryBuilderConnector([$draftPrData, $nonDraftPrData]);
    $builder = new QueryBuilder($connector);

    $results = $builder->repository('owner/repo')->whereDraft()->get();

    expect($results)->toBeArray()
        ->and($results)->toHaveCount(1)
        ->and($results[0]->data->isDraft())->toBeTrue();
});
