<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\PullRequest;
use ConduitUI\Pr\PullRequests;
use ConduitUI\Pr\QueryBuilder;
use ConduitUI\Pr\Services\GitHubPrService;
use Saloon\Http\Request;
use Saloon\Http\Response;

class PullRequestsFacadeMockResponse extends Response
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

class PullRequestsFacadeTestConnector extends Connector
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

        return new PullRequestsFacadeMockResponse($response);
    }
}

function createFacadeTestConnector(array $responses = []): Connector
{
    return new PullRequestsFacadeTestConnector($responses);
}

function createFacadeMockPrData(): array
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

beforeEach(function () {
    // Reset static state before each test
    PullRequests::setService(new GitHubPrService(createFacadeTestConnector([createFacadeMockPrData()])));
});

it('throws exception when connector not configured', function () {
    // Create a fresh instance without setting connector
    $reflection = new ReflectionClass(PullRequests::class);
    $serviceProperty = $reflection->getProperty('service');
    $serviceProperty->setAccessible(true);
    $serviceProperty->setValue(null, null);

    $connectorProperty = $reflection->getProperty('defaultConnector');
    $connectorProperty->setAccessible(true);
    $connectorProperty->setValue(null, null);

    PullRequests::find('owner/repo', 1);
})->throws(RuntimeException::class, 'PR service not configured');

it('can set connector via deprecated method', function () {
    $connector = createFacadeTestConnector([createFacadeMockPrData()]);
    PullRequests::setConnector($connector);

    $pr = PullRequests::find('owner/repo', 1);

    expect($pr)->toBeInstanceOf(PullRequest::class);
});

it('can set service directly', function () {
    $connector = createFacadeTestConnector([createFacadeMockPrData()]);
    $service = new GitHubPrService($connector);
    PullRequests::setService($service);

    $pr = PullRequests::find('owner/repo', 1);

    expect($pr)->toBeInstanceOf(PullRequest::class);
});

it('static for returns query builder', function () {
    $builder = PullRequests::for('owner/repo');

    expect($builder)->toBeInstanceOf(QueryBuilder::class);
});

it('static find returns pull request', function () {
    $connector = createFacadeTestConnector([createFacadeMockPrData()]);
    PullRequests::setConnector($connector);

    $pr = PullRequests::find('owner/repo', 1);

    expect($pr)->toBeInstanceOf(PullRequest::class);
});

it('static create returns pull request', function () {
    $connector = createFacadeTestConnector([createFacadeMockPrData()]);
    PullRequests::setConnector($connector);

    $pr = PullRequests::create('owner/repo', [
        'title' => 'Test',
        'head' => 'feature',
        'base' => 'main',
    ]);

    expect($pr)->toBeInstanceOf(PullRequest::class);
});

it('static query returns query builder', function () {
    $builder = PullRequests::query();

    expect($builder)->toBeInstanceOf(QueryBuilder::class);
});

it('instance get returns pull request', function () {
    $connector = createFacadeTestConnector([createFacadeMockPrData()]);
    $prs = new PullRequests($connector, 'owner', 'repo');

    $pr = $prs->get(1);

    expect($pr)->toBeInstanceOf(PullRequest::class);
});

it('instance list returns array of pull requests', function () {
    $connector = createFacadeTestConnector([[createFacadeMockPrData()]]);
    $prs = new PullRequests($connector, 'owner', 'repo');

    $results = $prs->list();

    expect($results)->toBeArray()
        ->and($results[0])->toBeInstanceOf(PullRequest::class);
});

it('instance listOpen returns open pull requests', function () {
    $connector = createFacadeTestConnector([[createFacadeMockPrData()]]);
    $prs = new PullRequests($connector, 'owner', 'repo');

    $results = $prs->listOpen();

    expect($results)->toBeArray();
});

it('instance listClosed returns closed pull requests', function () {
    $connector = createFacadeTestConnector([[createFacadeMockPrData()]]);
    $prs = new PullRequests($connector, 'owner', 'repo');

    $results = $prs->listClosed();

    expect($results)->toBeArray();
});

it('instance createPullRequest returns pull request', function () {
    $connector = createFacadeTestConnector([createFacadeMockPrData()]);
    $prs = new PullRequests($connector, 'owner', 'repo');

    $pr = $prs->createPullRequest([
        'title' => 'Test',
        'head' => 'feature',
        'base' => 'main',
    ]);

    expect($pr)->toBeInstanceOf(PullRequest::class);
});

it('instance update returns pull request', function () {
    $connector = createFacadeTestConnector([createFacadeMockPrData()]);
    $prs = new PullRequests($connector, 'owner', 'repo');

    $pr = $prs->update(1, ['title' => 'Updated']);

    expect($pr)->toBeInstanceOf(PullRequest::class);
});

it('instance merge returns boolean', function () {
    $connector = createFacadeTestConnector([['merged' => true]]);
    $prs = new PullRequests($connector, 'owner', 'repo');

    $result = $prs->merge(1, 'Merge commit', 'squash');

    expect($result)->toBeTrue();
});

it('instance close returns pull request', function () {
    $connector = createFacadeTestConnector([createFacadeMockPrData()]);
    $prs = new PullRequests($connector, 'owner', 'repo');

    $pr = $prs->close(1);

    expect($pr)->toBeInstanceOf(PullRequest::class);
});
