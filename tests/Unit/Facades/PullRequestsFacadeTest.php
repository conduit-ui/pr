<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\Facades\PullRequests as PullRequestsFacade;
use ConduitUI\Pr\PullRequests;
use ConduitUI\Pr\QueryBuilder;
use ConduitUI\Pr\Services\GitHubPrService;
use Illuminate\Support\Facades\Facade;
use Saloon\Http\Request;
use Saloon\Http\Response;

class LaravelFacadeMockResponse extends Response
{
    /**
     * @param  array<string, mixed>  $data
     */
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

class LaravelFacadeTestConnector extends Connector
{
    private int $callIndex = 0;

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $mockResponses = [];

    /**
     * @param  array<int, array<string, mixed>>  $responses
     */
    public function __construct(array $responses = [])
    {
        parent::__construct('test-token');
        $this->mockResponses = $responses;
    }

    public function send(Request $request, ...$args): Response
    {
        $response = $this->mockResponses[$this->callIndex++] ?? [];

        return new LaravelFacadeMockResponse($response);
    }
}

function createLaravelLaravelFacadeTestConnector(array $responses = []): Connector
{
    return new LaravelFacadeTestConnector($responses);
}

function createLaravelFacadeMockPrData(): array
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
        'merged' => false,
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

beforeEach(function () {
    // Set up the service for the facade
    $connector = createLaravelLaravelFacadeTestConnector([[createLaravelFacadeMockPrData()]]);
    $service = new GitHubPrService($connector);

    // Configure the underlying PullRequests class
    PullRequests::setService($service);
});

it('extends Laravel Facade', function () {
    expect(PullRequestsFacade::class)->toExtend(Facade::class);
});

it('has correct facade accessor', function () {
    expect((new ReflectionClass(PullRequestsFacade::class))
        ->getMethod('getFacadeAccessor')
        ->invoke(null))->toBe(PullRequests::class);
});

it('provides static open() shorthand method', function () {
    $query = PullRequests::open('owner/repo');

    expect($query)->toBeInstanceOf(QueryBuilder::class);
});

it('provides static closed() shorthand method', function () {
    $query = PullRequests::closed('owner/repo');

    expect($query)->toBeInstanceOf(QueryBuilder::class);
});

it('provides static merged() shorthand method', function () {
    $connector = createLaravelLaravelFacadeTestConnector([[array_merge(createLaravelFacadeMockPrData(), ['merged' => true])]]);
    $service = new GitHubPrService($connector);
    PullRequests::setService($service);

    $query = PullRequests::merged('owner/repo');

    expect($query)->toBeInstanceOf(QueryBuilder::class);
});

it('allows method chaining with shorthand methods', function () {
    $query = PullRequests::open('owner/repo')
        ->author('testuser')
        ->label('bug');

    expect($query)->toBeInstanceOf(QueryBuilder::class);
});

it('open() returns query builder with open state', function () {
    $connector = createLaravelLaravelFacadeTestConnector([[createLaravelFacadeMockPrData()]]);
    $service = new GitHubPrService($connector);
    PullRequests::setService($service);

    $query = PullRequests::open('owner/repo');
    $results = $query->get();

    expect($results)->toBeArray();
});

it('closed() returns query builder with closed state', function () {
    $connector = createLaravelLaravelFacadeTestConnector([[createLaravelFacadeMockPrData()]]);
    $service = new GitHubPrService($connector);
    PullRequests::setService($service);

    $query = PullRequests::closed('owner/repo');

    expect($query)->toBeInstanceOf(QueryBuilder::class);
});

it('merged() returns query builder for merged PRs', function () {
    $connector = createLaravelLaravelFacadeTestConnector([[array_merge(createLaravelFacadeMockPrData(), ['merged' => true, 'state' => 'closed'])]]);
    $service = new GitHubPrService($connector);
    PullRequests::setService($service);

    $query = PullRequests::merged('owner/repo');

    expect($query)->toBeInstanceOf(QueryBuilder::class);
});
