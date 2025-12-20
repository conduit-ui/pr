<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\Contracts\AssigneeManagerInterface;
use ConduitUI\Pr\DataTransferObjects\User;
use ConduitUI\Pr\Requests\AddAssignees;
use ConduitUI\Pr\Requests\GetPullRequest;
use ConduitUI\Pr\Requests\RemoveAssignees;
use Illuminate\Support\Collection;

final class AssigneeManager implements AssigneeManagerInterface
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

    public function get(): Collection
    {
        $response = $this->connector->send(new GetPullRequest(
            $this->owner,
            $this->repo,
            $this->prNumber
        ));

        /** @var array<string, mixed> $json */
        $json = $response->json();

        /** @var array<int, array<string, mixed>> $assignees */
        $assignees = $json['assignees'] ?? [];

        return collect($assignees)
            ->map(fn (array $assignee): User => User::fromArray($assignee));
    }

    public function add(string $username): self
    {
        return $this->addMany([$username]);
    }

    public function addMany(array $usernames): self
    {
        $this->connector->send(new AddAssignees(
            $this->owner,
            $this->repo,
            $this->prNumber,
            $usernames
        ));

        return $this;
    }

    public function remove(string $username): self
    {
        return $this->removeMany([$username]);
    }

    public function removeMany(array $usernames): self
    {
        $this->connector->send(new RemoveAssignees(
            $this->owner,
            $this->repo,
            $this->prNumber,
            $usernames
        ));

        return $this;
    }

    public function replace(array $usernames): self
    {
        $current = $this->get()->pluck('login')->toArray();

        if ($current !== []) {
            $this->removeMany($current);
        }

        if ($usernames !== []) {
            $this->addMany($usernames);
        }

        return $this;
    }

    public function clear(): self
    {
        $current = $this->get()->pluck('login')->toArray();

        if ($current !== []) {
            $this->removeMany($current);
        }

        return $this;
    }

    public function has(string $username): bool
    {
        return $this->get()
            ->contains('login', $username);
    }
}
