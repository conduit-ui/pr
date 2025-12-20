<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Services;

use Carbon\Carbon;
use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\Milestone;
use ConduitUI\Pr\Requests\CreateMilestone;
use ConduitUI\Pr\Requests\DeleteMilestone;
use ConduitUI\Pr\Requests\GetMilestone;
use ConduitUI\Pr\Requests\ListMilestones;
use ConduitUI\Pr\Requests\UpdateMilestone;
use Illuminate\Support\Collection;

final class RepositoryMilestoneManager
{
    protected string $owner;

    protected string $repo;

    public function __construct(
        protected Connector $connector,
        string $fullName,
    ) {
        [$this->owner, $this->repo] = explode('/', $fullName, 2);
    }

    /**
     * @return Collection<int, Milestone>
     */
    public function get(): Collection
    {
        $response = $this->connector->send(new ListMilestones(
            $this->owner,
            $this->repo,
            'all'
        ));

        /** @var array<int, array<string, mixed>> $milestones */
        $milestones = $response->json();

        return collect($milestones)
            ->map(fn (array $milestone): Milestone => Milestone::fromArray($milestone));
    }

    /**
     * @return Collection<int, Milestone>
     */
    public function whereOpen(): Collection
    {
        $response = $this->connector->send(new ListMilestones(
            $this->owner,
            $this->repo,
            'open'
        ));

        /** @var array<int, array<string, mixed>> $milestones */
        $milestones = $response->json();

        return collect($milestones)
            ->map(fn (array $milestone): Milestone => Milestone::fromArray($milestone));
    }

    /**
     * @return Collection<int, Milestone>
     */
    public function whereClosed(): Collection
    {
        $response = $this->connector->send(new ListMilestones(
            $this->owner,
            $this->repo,
            'closed'
        ));

        /** @var array<int, array<string, mixed>> $milestones */
        $milestones = $response->json();

        return collect($milestones)
            ->map(fn (array $milestone): Milestone => Milestone::fromArray($milestone));
    }

    public function find(int $number): Milestone
    {
        $response = $this->connector->send(new GetMilestone(
            $this->owner,
            $this->repo,
            $number
        ));

        /** @var array<string, mixed> $milestone */
        $milestone = $response->json();

        return Milestone::fromArray($milestone);
    }

    public function create(
        string $title,
        ?string $description = null,
        ?Carbon $dueOn = null,
        string $state = 'open'
    ): Milestone {
        $data = [
            'title' => $title,
            'state' => $state,
        ];

        if ($description !== null) {
            $data['description'] = $description;
        }

        if ($dueOn !== null) {
            $data['due_on'] = $dueOn->toIso8601String();
        }

        $response = $this->connector->send(new CreateMilestone(
            $this->owner,
            $this->repo,
            $data
        ));

        /** @var array<string, mixed> $milestone */
        $milestone = $response->json();

        return Milestone::fromArray($milestone);
    }

    public function update(
        int $number,
        ?string $title = null,
        ?string $description = null,
        ?Carbon $dueOn = null,
        ?string $state = null
    ): Milestone {
        $data = array_filter([
            'title' => $title,
            'description' => $description,
            'due_on' => $dueOn?->toIso8601String(),
            'state' => $state,
        ], fn ($value): bool => $value !== null);

        $response = $this->connector->send(new UpdateMilestone(
            $this->owner,
            $this->repo,
            $number,
            $data
        ));

        /** @var array<string, mixed> $milestone */
        $milestone = $response->json();

        return Milestone::fromArray($milestone);
    }

    public function delete(int $number): bool
    {
        $response = $this->connector->send(new DeleteMilestone(
            $this->owner,
            $this->repo,
            $number
        ));

        return $response->successful();
    }
}
