<?php

declare(strict_types=1);

namespace ConduitUI\Pr;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\Contracts\PrServiceInterface;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\Requests\CreatePullRequest;
use ConduitUI\Pr\Requests\GetPullRequest;
use ConduitUI\Pr\Requests\ListPullRequests;
use ConduitUI\Pr\Requests\MergePullRequest;
use ConduitUI\Pr\Requests\UpdatePullRequest;
use ConduitUI\Pr\Services\GitHubPrService;

class PullRequests
{
    protected static ?Connector $defaultConnector = null;

    protected static ?PrServiceInterface $service = null;

    public function __construct(
        protected Connector $connector,
        protected string $owner,
        protected string $repo
    ) {}

    /**
     * Set the PR service for static methods
     */
    public static function setService(PrServiceInterface $service): void
    {
        self::$service = $service;
    }

    /**
     * Set the default GitHub connector for static methods
     *
     * @deprecated Use setService(new GitHubPrService($connector)) instead
     */
    public static function setConnector(Connector $connector): void
    {
        self::$defaultConnector = $connector;
        self::$service = new GitHubPrService($connector);
    }

    /**
     * Get the default connector or throw an exception
     */
    protected static function connector(): Connector
    {
        if (self::$defaultConnector === null) {
            throw new \RuntimeException(
                'GitHub connector not configured. Call PullRequests::setConnector() or setService() first.'
            );
        }

        return self::$defaultConnector;
    }

    /**
     * Get the PR service or throw an exception
     */
    protected static function service(): PrServiceInterface
    {
        if (self::$service === null) {
            throw new \RuntimeException(
                'PR service not configured. Call PullRequests::setService() or setConnector() first.'
            );
        }

        return self::$service;
    }

    /**
     * Create a fluent query builder for a repository
     */
    public static function for(string $repository): QueryBuilder
    {
        return self::service()->for($repository);
    }

    /**
     * Find a specific pull request by number
     */
    public static function find(string $repository, int $number): PullRequest
    {
        return self::service()->find($repository, $number);
    }

    /**
     * Create a new pull request
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function create(string $repository, array $attributes): PullRequest
    {
        return self::service()->create($repository, $attributes);
    }

    /**
     * Create a new query builder
     */
    public static function query(): QueryBuilder
    {
        return self::service()->query();
    }

    /**
     * Get a pull request by number
     */
    public function get(int $number): PullRequest
    {
        $response = $this->connector->send(new GetPullRequest(
            $this->owner,
            $this->repo,
            $number
        ));

        return new PullRequest(
            $this->connector,
            $this->owner,
            $this->repo,
            PullRequestData::fromArray($response->json())
        );
    }

    /**
     * List pull requests with optional filters
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, PullRequest>
     */
    public function list(array $filters = []): array
    {
        $mergedFilters = array_merge([
            'state' => 'open',
            'sort' => 'created',
            'direction' => 'desc',
        ], $filters);

        $response = $this->connector->send(new ListPullRequests(
            $this->owner,
            $this->repo,
            $mergedFilters
        ));

        return array_values(array_map(
            fn ($pr) => new PullRequest(
                $this->connector,
                $this->owner,
                $this->repo,
                PullRequestData::fromArray($pr)
            ),
            $response->json()
        ));
    }

    /**
     * Get only open pull requests
     *
     * @return array<int, PullRequest>
     */
    public function open(): array
    {
        return $this->list(['state' => 'open']);
    }

    /**
     * Get only closed pull requests
     *
     * @return array<int, PullRequest>
     */
    public function closed(): array
    {
        return $this->list(['state' => 'closed']);
    }

    /**
     * Create a new pull request (instance method)
     *
     * @param  array<string, mixed>  $data
     */
    public function createPullRequest(array $data): PullRequest
    {
        $response = $this->connector->send(new CreatePullRequest(
            $this->owner,
            $this->repo,
            $data
        ));

        return new PullRequest(
            $this->connector,
            $this->owner,
            $this->repo,
            PullRequestData::fromArray($response->json())
        );
    }

    /**
     * Update a pull request
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $number, array $data): PullRequest
    {
        $response = $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $number,
            $data
        ));

        return new PullRequest(
            $this->connector,
            $this->owner,
            $this->repo,
            PullRequestData::fromArray($response->json())
        );
    }

    /**
     * Merge a pull request
     */
    public function merge(int $number, ?string $commitMessage = null, ?string $mergeMethod = null): bool
    {
        $data = array_filter([
            'commit_message' => $commitMessage,
            'merge_method' => $mergeMethod,
        ]);

        $response = $this->connector->send(new MergePullRequest(
            $this->owner,
            $this->repo,
            $number,
            $data
        ));

        return $response->json()['merged'] ?? false;
    }

    /**
     * Close a pull request
     */
    public function close(int $number): PullRequest
    {
        return $this->update($number, ['state' => 'closed']);
    }
}
