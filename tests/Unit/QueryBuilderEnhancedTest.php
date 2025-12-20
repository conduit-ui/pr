<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\QueryBuilder;
use Saloon\Http\Request;
use Saloon\Http\Response;

class EnhancedQueryBuilderMockResponse extends Response
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

class EnhancedQueryBuilderTestConnector extends Connector
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
        return new EnhancedQueryBuilderMockResponse($this->mockResponse);
    }
}

function createEnhancedConnector(array $response = []): Connector
{
    return new EnhancedQueryBuilderTestConnector($response);
}

function createEnhancedMockPrData(array $overrides = []): array
{
    $defaults = [
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
        'labels' => [],
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

    return array_merge($defaults, $overrides);
}

describe('QueryBuilder enhanced features', function (): void {
    it('can use repo() alias for repository()', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repo('owner/repo');

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can use whereLabel() alias', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->whereLabel('bug');

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can filter by multiple labels (match any)', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->whereLabels(['bug', 'feature']);

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can filter by all labels (match all)', function () {
        $prWithLabels = createEnhancedMockPrData([
            'labels' => [
                ['id' => 1, 'name' => 'bug', 'color' => 'red', 'description' => null],
                ['id' => 2, 'name' => 'priority', 'color' => 'yellow', 'description' => null],
            ],
        ]);

        $connector = createEnhancedConnector([$prWithLabels]);
        $builder = new QueryBuilder($connector);

        $results = $builder->repository('owner/repo')->whereAllLabels(['bug', 'priority'])->get();

        expect($results)->toBeArray()
            ->and($results)->toHaveCount(1);
    });

    it('filters out PRs that do not have all required labels', function () {
        $prWithSomeLabels = createEnhancedMockPrData([
            'number' => 1,
            'labels' => [
                ['id' => 1, 'name' => 'bug', 'color' => 'red', 'description' => null],
            ],
        ]);

        $prWithAllLabels = createEnhancedMockPrData([
            'number' => 2,
            'labels' => [
                ['id' => 1, 'name' => 'bug', 'color' => 'red', 'description' => null],
                ['id' => 2, 'name' => 'priority', 'color' => 'yellow', 'description' => null],
            ],
        ]);

        $connector = createEnhancedConnector([$prWithSomeLabels, $prWithAllLabels]);
        $builder = new QueryBuilder($connector);

        $results = $builder->repository('owner/repo')->whereAllLabels(['bug', 'priority'])->get();

        expect($results)->toBeArray()
            ->and($results)->toHaveCount(1)
            ->and($results[0]->data->number)->toBe(2);
    });

    it('can check if results exist', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $exists = $builder->repository('owner/repo')->exists();

        expect($exists)->toBeTrue();
    });

    it('returns false when no results exist', function () {
        $connector = createEnhancedConnector([]);
        $builder = new QueryBuilder($connector);

        $exists = $builder->repository('owner/repo')->exists();

        expect($exists)->toBeFalse();
    });

    it('can pluck a specific field from results', function () {
        $pr1 = createEnhancedMockPrData(['number' => 1, 'title' => 'First PR']);
        $pr2 = createEnhancedMockPrData(['number' => 2, 'title' => 'Second PR']);

        $connector = createEnhancedConnector([$pr1, $pr2]);
        $builder = new QueryBuilder($connector);

        $titles = $builder->repository('owner/repo')->pluck('title');

        expect($titles)->toBeArray()
            ->and($titles)->toHaveCount(2)
            ->and($titles[0])->toBe('First PR')
            ->and($titles[1])->toBe('Second PR');
    });

    it('can use whereOpen() alias', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->whereOpen();

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can use whereClosed() alias', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->whereClosed();

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can use whereState() alias', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->whereState('all');

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can use whereAuthor() alias', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->whereAuthor('testuser');

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can order by created date', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->orderByCreated('asc');

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can order by updated date', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->orderByUpdated('desc');

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can order by popularity', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->orderByPopularity();

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can order by long running', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->orderByLongRunning();

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can use perPage() alias for take()', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $result = $builder->repository('owner/repo')->perPage(50);

        expect($result)->toBeInstanceOf(QueryBuilder::class);
    });

    it('can chain complex query with new methods', function () {
        $connector = createEnhancedConnector([createEnhancedMockPrData()]);
        $builder = new QueryBuilder($connector);

        $results = $builder
            ->repo('owner/repo')
            ->whereOpen()
            ->whereAuthor('testuser')
            ->whereLabels(['bug', 'priority'])
            ->orderByUpdated('desc')
            ->perPage(25)
            ->get();

        expect($results)->toBeArray();
    });

    it('returns empty array when plucking from no results', function () {
        $connector = createEnhancedConnector([]);
        $builder = new QueryBuilder($connector);

        $titles = $builder->repository('owner/repo')->pluck('title');

        expect($titles)->toBeArray()
            ->and($titles)->toHaveCount(0);
    });

    it('handles empty labels array in whereAllLabels', function () {
        $prWithNoLabels = createEnhancedMockPrData([
            'labels' => [],
        ]);

        $connector = createEnhancedConnector([$prWithNoLabels]);
        $builder = new QueryBuilder($connector);

        $results = $builder->repository('owner/repo')->whereAllLabels(['bug'])->get();

        expect($results)->toBeArray()
            ->and($results)->toHaveCount(0);
    });
});
