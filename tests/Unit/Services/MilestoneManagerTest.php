<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\Milestone;
use ConduitUI\Pr\Requests\UpdatePullRequest;
use ConduitUI\Pr\Services\MilestoneManager;
use Saloon\Http\Request;
use Saloon\Http\Response;

class MockMilestoneManagerResponse extends Response
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

class MilestoneManagerTestConnector extends Connector
{
    public ?Request $lastRequest = null;

    public function __construct(private ?array $milestone = null)
    {
        parent::__construct('test-token');
    }

    public function send(Request $request, ...$args): Response
    {
        $this->lastRequest = $request;

        return new MockMilestoneManagerResponse([
            'milestone' => $this->milestone,
        ]);
    }
}

it('can get milestone', function () {
    $connector = new MilestoneManagerTestConnector([
        'number' => 1,
        'title' => 'v1.0',
        'description' => 'First release',
        'state' => 'open',
        'open_issues' => 5,
        'closed_issues' => 10,
        'due_on' => '2025-12-31T23:59:59Z',
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    $manager = new MilestoneManager($connector, 'test/repo', 123);
    $milestone = $manager->get();

    expect($milestone)->toBeInstanceOf(Milestone::class)
        ->and($milestone->number)->toBe(1)
        ->and($milestone->title)->toBe('v1.0');
});

it('returns null when no milestone exists', function () {
    $connector = new MilestoneManagerTestConnector(null);

    $manager = new MilestoneManager($connector, 'test/repo', 123);
    $milestone = $manager->get();

    expect($milestone)->toBeNull();
});

it('can set milestone', function () {
    $connector = new MilestoneManagerTestConnector([
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

    $manager = new MilestoneManager($connector, 'test/repo', 123);
    $milestone = $manager->set(5);

    expect($milestone)->toBeInstanceOf(Milestone::class)
        ->and($milestone->number)->toBe(5)
        ->and($connector->lastRequest)->toBeInstanceOf(UpdatePullRequest::class);
});

it('can remove milestone', function () {
    $connector = new MilestoneManagerTestConnector(null);

    $manager = new MilestoneManager($connector, 'test/repo', 123);
    $result = $manager->remove();

    expect($result)->toBeTrue()
        ->and($connector->lastRequest)->toBeInstanceOf(UpdatePullRequest::class);
});
