<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\Contracts\PrServiceInterface;
use ConduitUI\Pr\PullRequest;
use ConduitUI\Pr\QueryBuilder;
use ConduitUI\Pr\Services\GitHubPrService;
use Saloon\Http\Request;
use Saloon\Http\Response;

class MockServiceResponse extends Response
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

class TestServiceConnector extends Connector
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

        return new MockServiceResponse($responseData);
    }
}

function createServiceTestConnector(array $responses = []): Connector
{
    return new TestServiceConnector($responses);
}

it('implements PrServiceInterface', function () {
    $connector = createServiceTestConnector();
    $service = new GitHubPrService($connector);

    expect($service)->toBeInstanceOf(PrServiceInterface::class);
});

it('can find a pull request', function () {
    $prData = [
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
    ];

    $connector = createServiceTestConnector([$prData]);
    $service = new GitHubPrService($connector);

    $pr = $service->find('owner/repo', 123);

    expect($pr)->toBeInstanceOf(PullRequest::class)
        ->and($pr->number)->toBe(123)
        ->and($pr->title)->toBe('Test PR');
});

it('can create a pull request', function () {
    $prData = [
        'number' => 124,
        'title' => 'New PR',
        'body' => 'New description',
        'state' => 'open',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'html_url' => 'https://github.com/owner/repo/pull/124',
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
    ];

    $connector = createServiceTestConnector([$prData]);
    $service = new GitHubPrService($connector);

    $pr = $service->create('owner/repo', [
        'title' => 'New PR',
        'body' => 'New description',
        'head' => 'feature-branch',
        'base' => 'main',
    ]);

    expect($pr)->toBeInstanceOf(PullRequest::class)
        ->and($pr->number)->toBe(124)
        ->and($pr->title)->toBe('New PR');
});

it('returns a query builder for a repository', function () {
    $connector = createServiceTestConnector();
    $service = new GitHubPrService($connector);

    $queryBuilder = $service->for('owner/repo');

    expect($queryBuilder)->toBeInstanceOf(QueryBuilder::class);
});

it('returns a query builder without repository', function () {
    $connector = createServiceTestConnector();
    $service = new GitHubPrService($connector);

    $queryBuilder = $service->query();

    expect($queryBuilder)->toBeInstanceOf(QueryBuilder::class);
});

it('can update a pull request', function () {
    $prData = [
        'number' => 123,
        'title' => 'Updated PR',
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
    ];

    $connector = createServiceTestConnector([$prData]);
    $service = new GitHubPrService($connector);

    $pr = $service->update('owner/repo', 123, ['title' => 'Updated PR']);

    expect($pr)->toBeInstanceOf(PullRequest::class)
        ->and($pr->title)->toBe('Updated PR');
});

it('can merge a pull request', function () {
    $connector = createServiceTestConnector([['merged' => true]]);
    $service = new GitHubPrService($connector);

    $result = $service->merge('owner/repo', 123, 'Merge message', 'squash');

    expect($result)->toBeTrue();
});

it('can close a pull request', function () {
    $prData = [
        'number' => 123,
        'title' => 'Test PR',
        'body' => 'Test description',
        'state' => 'closed',
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
    ];

    $connector = createServiceTestConnector([$prData]);
    $service = new GitHubPrService($connector);

    $pr = $service->close('owner/repo', 123);

    expect($pr)->toBeInstanceOf(PullRequest::class)
        ->and($pr->state)->toBe('closed');
});

it('can list pull requests', function () {
    $prData = [
        [
            'number' => 123,
            'title' => 'Test PR 1',
            'body' => 'Test description 1',
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
        ],
        [
            'number' => 124,
            'title' => 'Test PR 2',
            'body' => 'Test description 2',
            'state' => 'open',
            'user' => [
                'id' => 1,
                'login' => 'testuser',
                'avatar_url' => 'https://example.com/avatar.jpg',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'html_url' => 'https://github.com/owner/repo/pull/124',
            'created_at' => '2025-01-01T00:00:00Z',
            'updated_at' => '2025-01-01T00:00:00Z',
            'draft' => false,
            'head' => [
                'ref' => 'another-branch',
                'sha' => 'xyz789',
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
        ],
    ];

    $connector = createServiceTestConnector([$prData]);
    $service = new GitHubPrService($connector);

    $prs = $service->list('owner/repo');

    expect($prs)->toBeArray()
        ->and(count($prs))->toBe(2)
        ->and($prs[0])->toBeInstanceOf(PullRequest::class)
        ->and($prs[0]->number)->toBe(123)
        ->and($prs[1])->toBeInstanceOf(PullRequest::class)
        ->and($prs[1]->number)->toBe(124);
});
