<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\Milestone;
use ConduitUI\Pr\Requests\GetPullRequest;
use ConduitUI\Pr\Requests\UpdatePullRequest;

final class MilestoneManager
{
    protected string $owner;

    protected string $repo;

    public function __construct(
        protected Connector $connector,
        string $fullName,
        protected int $prNumber,
    ) {
        [$this->owner, $this->repo] = explode('/', $fullName, 2);
    }

    public function get(): ?Milestone
    {
        $response = $this->connector->send(new GetPullRequest(
            $this->owner,
            $this->repo,
            $this->prNumber
        ));

        /** @var array<string, mixed> $json */
        $json = $response->json();

        /** @var array<string, mixed>|null $milestone */
        $milestone = $json['milestone'] ?? null;

        return $milestone !== null ? Milestone::fromArray($milestone) : null;
    }

    public function set(int $milestoneNumber): Milestone
    {
        $response = $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->prNumber,
            ['milestone' => $milestoneNumber]
        ));

        /** @var array<string, mixed> $json */
        $json = $response->json();

        /** @var array<string, mixed> $milestone */
        $milestone = $json['milestone'];

        return Milestone::fromArray($milestone);
    }

    public function remove(): bool
    {
        $response = $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->prNumber,
            ['milestone' => null]
        ));

        return $response->successful();
    }
}
