<?php

declare(strict_types=1);

use Carbon\Carbon;
use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\Base;
use ConduitUI\Pr\DataTransferObjects\Head;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\DataTransferObjects\Repository;
use ConduitUI\Pr\DataTransferObjects\User;
use ConduitUI\Pr\PullRequest;
use ConduitUI\Pr\Requests\UpdatePullRequest;
use ConduitUI\Pr\Services\AssigneeManager;
use ConduitUI\Pr\Services\MilestoneManager;
use Saloon\Http\Request;
use Saloon\Http\Response;

class MockPullRequestAssigneeResponse extends Response
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

    public function successful(): bool
    {
        return true;
    }
}

class PullRequestAssigneeTestConnector extends Connector
{
    public ?Request $lastRequest = null;

    public function __construct(private ?array $milestone = null)
    {
        parent::__construct('test-token');
    }

    public function send(Request $request, ...$args): Response
    {
        $this->lastRequest = $request;

        return new MockPullRequestAssigneeResponse([
            'milestone' => $this->milestone,
        ]);
    }
}

beforeEach(function () {
    $this->prData = new PullRequestData(
        number: 123,
        title: 'Test PR',
        body: 'Test body',
        state: 'open',
        user: new User(1, 'testuser', 'https://example.com/avatar.jpg', 'https://github.com/testuser', 'User'),
        htmlUrl: 'https://github.com/test/test-repo/pull/123',
        createdAt: Carbon::parse('2025-01-01')->toDateTimeImmutable(),
        updatedAt: Carbon::parse('2025-01-15')->toDateTimeImmutable(),
        closedAt: null,
        mergedAt: null,
        mergeCommitSha: null,
        draft: false,
        additions: 10,
        deletions: 5,
        changedFiles: 2,
        assignee: null,
        assignees: [],
        requestedReviewers: [],
        labels: [],
        head: new Head(
            'feature',
            'abc123',
            new User(1, 'testuser', 'https://example.com/avatar.jpg', 'https://github.com/testuser', 'User'),
            new Repository(1, 'test-repo', 'test/test-repo', 'https://github.com/test/test-repo', false)
        ),
        base: new Base(
            'main',
            'def456',
            new User(1, 'testuser', 'https://example.com/avatar.jpg', 'https://github.com/testuser', 'User'),
            new Repository(1, 'test-repo', 'test/test-repo', 'https://github.com/test/test-repo', false)
        ),
    );
});

it('returns assignee manager instance', function () {
    $connector = new PullRequestAssigneeTestConnector;
    $pr = new PullRequest($connector, 'test', 'repo', $this->prData);

    $assignees = $pr->assignees();

    expect($assignees)->toBeInstanceOf(AssigneeManager::class);
});

it('returns milestone manager instance', function () {
    $connector = new PullRequestAssigneeTestConnector;
    $pr = new PullRequest($connector, 'test', 'repo', $this->prData);

    $milestone = $pr->milestone();

    expect($milestone)->toBeInstanceOf(MilestoneManager::class);
});

it('can set milestone on pull request', function () {
    $connector = new PullRequestAssigneeTestConnector([
        'number' => 5,
        'title' => 'v2.0',
        'description' => 'Second release',
        'state' => 'open',
        'open_issues' => 3,
        'closed_issues' => 7,
        'due_on' => '2025-06-30T23:59:59Z',
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/test/repo/milestone/5',
    ]);

    $pr = new PullRequest($connector, 'test', 'repo', $this->prData);
    $result = $pr->setMilestone(5);

    expect($result)->toBeInstanceOf(PullRequest::class)
        ->and($connector->lastRequest)->toBeInstanceOf(UpdatePullRequest::class);
});
