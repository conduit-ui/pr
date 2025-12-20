<?php

declare(strict_types=1);

use Carbon\Carbon;
use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\Milestone;
use ConduitUI\Pr\Requests\CreateMilestone;
use ConduitUI\Pr\Requests\DeleteMilestone;
use ConduitUI\Pr\Requests\GetMilestone;
use ConduitUI\Pr\Requests\ListMilestones;
use ConduitUI\Pr\Requests\UpdateMilestone;
use ConduitUI\Pr\Services\RepositoryMilestoneManager;
use Saloon\Http\Request;
use Saloon\Http\Response;

class MockRepositoryMilestoneManagerResponse extends Response
{
    public function __construct(private array|bool $data = [])
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
        return $this->data !== false;
    }
}

class RepositoryMilestoneManagerTestConnector extends Connector
{
    public ?Request $lastRequest = null;

    public function __construct(
        private array $milestones = [],
        private ?array $singleMilestone = null,
        private bool $operationSuccess = true
    ) {
        parent::__construct('test-token');
    }

    public function send(Request $request, ...$args): Response
    {
        $this->lastRequest = $request;

        if ($request instanceof ListMilestones) {
            return new MockRepositoryMilestoneManagerResponse($this->milestones);
        }

        if ($request instanceof GetMilestone || $request instanceof CreateMilestone || $request instanceof UpdateMilestone) {
            return new MockRepositoryMilestoneManagerResponse($this->singleMilestone ?? []);
        }

        if ($request instanceof DeleteMilestone) {
            return new MockRepositoryMilestoneManagerResponse($this->operationSuccess);
        }

        return new MockRepositoryMilestoneManagerResponse([]);
    }
}

it('can get all milestones', function () {
    $connector = new RepositoryMilestoneManagerTestConnector([
        [
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
        ],
        [
            'number' => 2,
            'title' => 'v2.0',
            'description' => 'Second release',
            'state' => 'closed',
            'open_issues' => 0,
            'closed_issues' => 15,
            'due_on' => null,
            'created_at' => '2025-01-01T00:00:00Z',
            'updated_at' => '2025-01-15T00:00:00Z',
            'closed_at' => '2025-01-14T00:00:00Z',
            'html_url' => 'https://github.com/test/repo/milestone/2',
        ],
    ]);

    $manager = new RepositoryMilestoneManager($connector, 'test/repo');
    $milestones = $manager->get();

    expect($milestones)->toHaveCount(2)
        ->and($milestones->first())->toBeInstanceOf(Milestone::class)
        ->and($milestones->first()->title)->toBe('v1.0')
        ->and($milestones->last()->title)->toBe('v2.0');
});

it('can get open milestones', function () {
    $connector = new RepositoryMilestoneManagerTestConnector([
        [
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
        ],
    ]);

    $manager = new RepositoryMilestoneManager($connector, 'test/repo');
    $milestones = $manager->whereOpen();

    expect($milestones)->toHaveCount(1)
        ->and($milestones->first()->state)->toBe('open');
});

it('can get closed milestones', function () {
    $connector = new RepositoryMilestoneManagerTestConnector([
        [
            'number' => 2,
            'title' => 'v2.0',
            'description' => 'Second release',
            'state' => 'closed',
            'open_issues' => 0,
            'closed_issues' => 15,
            'due_on' => null,
            'created_at' => '2025-01-01T00:00:00Z',
            'updated_at' => '2025-01-15T00:00:00Z',
            'closed_at' => '2025-01-14T00:00:00Z',
            'html_url' => 'https://github.com/test/repo/milestone/2',
        ],
    ]);

    $manager = new RepositoryMilestoneManager($connector, 'test/repo');
    $milestones = $manager->whereClosed();

    expect($milestones)->toHaveCount(1)
        ->and($milestones->first()->state)->toBe('closed');
});

it('can find specific milestone', function () {
    $connector = new RepositoryMilestoneManagerTestConnector(
        [],
        [
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
        ]
    );

    $manager = new RepositoryMilestoneManager($connector, 'test/repo');
    $milestone = $manager->find(1);

    expect($milestone)->toBeInstanceOf(Milestone::class)
        ->and($milestone->number)->toBe(1)
        ->and($connector->lastRequest)->toBeInstanceOf(GetMilestone::class);
});

it('can create milestone with all parameters', function () {
    $connector = new RepositoryMilestoneManagerTestConnector(
        [],
        [
            'number' => 3,
            'title' => 'v3.0',
            'description' => 'Third release',
            'state' => 'open',
            'open_issues' => 0,
            'closed_issues' => 0,
            'due_on' => '2025-12-31T23:59:59Z',
            'created_at' => '2025-01-15T00:00:00Z',
            'updated_at' => '2025-01-15T00:00:00Z',
            'closed_at' => null,
            'html_url' => 'https://github.com/test/repo/milestone/3',
        ]
    );

    $manager = new RepositoryMilestoneManager($connector, 'test/repo');
    $milestone = $manager->create(
        'v3.0',
        'Third release',
        Carbon::parse('2025-12-31T23:59:59Z'),
        'open'
    );

    expect($milestone)->toBeInstanceOf(Milestone::class)
        ->and($milestone->title)->toBe('v3.0')
        ->and($connector->lastRequest)->toBeInstanceOf(CreateMilestone::class);
});

it('can create milestone with minimal parameters', function () {
    $connector = new RepositoryMilestoneManagerTestConnector(
        [],
        [
            'number' => 4,
            'title' => 'v4.0',
            'description' => null,
            'state' => 'open',
            'open_issues' => 0,
            'closed_issues' => 0,
            'due_on' => null,
            'created_at' => '2025-01-15T00:00:00Z',
            'updated_at' => '2025-01-15T00:00:00Z',
            'closed_at' => null,
            'html_url' => 'https://github.com/test/repo/milestone/4',
        ]
    );

    $manager = new RepositoryMilestoneManager($connector, 'test/repo');
    $milestone = $manager->create('v4.0');

    expect($milestone)->toBeInstanceOf(Milestone::class)
        ->and($milestone->title)->toBe('v4.0')
        ->and($milestone->description)->toBeNull();
});

it('can update milestone', function () {
    $connector = new RepositoryMilestoneManagerTestConnector(
        [],
        [
            'number' => 1,
            'title' => 'v1.0 Updated',
            'description' => 'Updated description',
            'state' => 'closed',
            'open_issues' => 0,
            'closed_issues' => 15,
            'due_on' => '2025-12-31T23:59:59Z',
            'created_at' => '2025-01-01T00:00:00Z',
            'updated_at' => '2025-01-15T12:00:00Z',
            'closed_at' => '2025-01-15T12:00:00Z',
            'html_url' => 'https://github.com/test/repo/milestone/1',
        ]
    );

    $manager = new RepositoryMilestoneManager($connector, 'test/repo');
    $milestone = $manager->update(
        1,
        'v1.0 Updated',
        'Updated description',
        Carbon::parse('2025-12-31T23:59:59Z'),
        'closed'
    );

    expect($milestone)->toBeInstanceOf(Milestone::class)
        ->and($milestone->title)->toBe('v1.0 Updated')
        ->and($connector->lastRequest)->toBeInstanceOf(UpdateMilestone::class);
});

it('can update milestone with partial parameters', function () {
    $connector = new RepositoryMilestoneManagerTestConnector(
        [],
        [
            'number' => 1,
            'title' => 'v1.0',
            'description' => 'First release',
            'state' => 'closed',
            'open_issues' => 0,
            'closed_issues' => 15,
            'due_on' => '2025-12-31T23:59:59Z',
            'created_at' => '2025-01-01T00:00:00Z',
            'updated_at' => '2025-01-15T12:00:00Z',
            'closed_at' => '2025-01-15T12:00:00Z',
            'html_url' => 'https://github.com/test/repo/milestone/1',
        ]
    );

    $manager = new RepositoryMilestoneManager($connector, 'test/repo');
    $milestone = $manager->update(1, null, null, null, 'closed');

    expect($milestone)->toBeInstanceOf(Milestone::class)
        ->and($milestone->state)->toBe('closed');
});

it('can delete milestone', function () {
    $connector = new RepositoryMilestoneManagerTestConnector([], null, true);

    $manager = new RepositoryMilestoneManager($connector, 'test/repo');
    $result = $manager->delete(1);

    expect($result)->toBeTrue()
        ->and($connector->lastRequest)->toBeInstanceOf(DeleteMilestone::class);
});
