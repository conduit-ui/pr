<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\Contracts\PrServiceInterface;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\PullRequest;
use ConduitUI\Pr\QueryBuilder;
use ConduitUI\Pr\Requests\CreatePullRequest;
use ConduitUI\Pr\Requests\GetPullRequest;
use ConduitUI\Pr\Requests\ListPullRequests;
use ConduitUI\Pr\Requests\MergePullRequest;
use ConduitUI\Pr\Requests\UpdatePullRequest;

class GitHubPrService implements PrServiceInterface
{
    public function __construct(
        protected Connector $connector
    ) {}

    public function find(string $repository, int $number): PullRequest
    {
        [$owner, $repo] = explode('/', $repository, 2);

        $response = $this->connector->send(new GetPullRequest($owner, $repo, $number));

        return new PullRequest(
            $this->connector,
            $owner,
            $repo,
            PullRequestData::fromArray($response->json())
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(string $repository, array $attributes): PullRequest
    {
        [$owner, $repo] = explode('/', $repository, 2);

        $response = $this->connector->send(new CreatePullRequest($owner, $repo, $attributes));

        return new PullRequest(
            $this->connector,
            $owner,
            $repo,
            PullRequestData::fromArray($response->json())
        );
    }

    public function for(string $repository): QueryBuilder
    {
        return (new QueryBuilder($this->connector))->repository($repository)->open();
    }

    public function query(): QueryBuilder
    {
        return new QueryBuilder($this->connector);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $repository, int $number, array $data): PullRequest
    {
        [$owner, $repo] = explode('/', $repository, 2);

        $response = $this->connector->send(new UpdatePullRequest($owner, $repo, $number, $data));

        return new PullRequest(
            $this->connector,
            $owner,
            $repo,
            PullRequestData::fromArray($response->json())
        );
    }

    public function merge(string $repository, int $number, ?string $commitMessage = null, ?string $mergeMethod = null): bool
    {
        [$owner, $repo] = explode('/', $repository, 2);

        $data = array_filter([
            'commit_message' => $commitMessage,
            'merge_method' => $mergeMethod,
        ]);

        $response = $this->connector->send(new MergePullRequest($owner, $repo, $number, $data));

        return $response->json()['merged'] ?? false;
    }

    public function close(string $repository, int $number): PullRequest
    {
        return $this->update($repository, $number, ['state' => 'closed']);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, PullRequest>
     */
    public function list(string $repository, array $filters = []): array
    {
        [$owner, $repo] = explode('/', $repository, 2);

        $mergedFilters = array_merge([
            'state' => 'open',
            'sort' => 'created',
            'direction' => 'desc',
        ], $filters);

        $response = $this->connector->send(new ListPullRequests($owner, $repo, $mergedFilters));

        return array_values(array_map(
            fn ($pr) => new PullRequest(
                $this->connector,
                $owner,
                $repo,
                PullRequestData::fromArray($pr)
            ),
            $response->json()
        ));
    }
}
