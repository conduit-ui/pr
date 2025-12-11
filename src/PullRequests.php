<?php

declare(strict_types=1);

namespace ConduitUI\Pr;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;

class PullRequests
{
    protected static ?Connector $defaultConnector = null;

    public function __construct(
        protected Connector $connector,
        protected string $owner,
        protected string $repo
    ) {
    }

    /**
     * Set the default GitHub connector for static methods
     */
    public static function setConnector(Connector $connector): void
    {
        self::$defaultConnector = $connector;
    }

    /**
     * Get the default connector or throw an exception
     */
    protected static function connector(): Connector
    {
        if (self::$defaultConnector === null) {
            throw new \RuntimeException(
                'GitHub connector not configured. Call PullRequests::setConnector() first.'
            );
        }

        return self::$defaultConnector;
    }

    /**
     * Create a fluent query builder for a repository
     */
    public static function for(string $repository): QueryBuilder
    {
        return (new QueryBuilder(self::connector()))->repository($repository)->open();
    }

    /**
     * Find a specific pull request by number
     */
    public static function find(string $repository, int $number): PullRequest
    {
        [$owner, $repo] = explode('/', $repository, 2);
        $connector = self::connector();

        $response = $connector->get("/repos/{$owner}/{$repo}/pulls/{$number}");

        return new PullRequest($connector, $owner, $repo, PullRequestData::fromArray($response));
    }

    /**
     * Create a new pull request
     */
    public static function create(string $repository, array $attributes): PullRequest
    {
        [$owner, $repo] = explode('/', $repository, 2);
        $connector = self::connector();

        $response = $connector->post("/repos/{$owner}/{$repo}/pulls", $attributes);

        return new PullRequest($connector, $owner, $repo, PullRequestData::fromArray($response));
    }

    /**
     * Create a new query builder
     */
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(self::connector());
    }

    /**
     * Get a pull request by number
     */
    public function get(int $number): PullRequest
    {
        $response = $this->connector->get("/repos/{$this->owner}/{$this->repo}/pulls/{$number}");

        return new PullRequest($this->connector, $this->owner, $this->repo, PullRequestData::fromArray($response));
    }

    /**
     * List pull requests with optional filters
     */
    public function list(array $filters = []): array
    {
        $queryParams = http_build_query(array_merge([
            'state' => 'open',
            'sort' => 'created',
            'direction' => 'desc',
        ], $filters));

        $response = $this->connector->get("/repos/{$this->owner}/{$this->repo}/pulls?{$queryParams}");

        return array_map(
            fn ($pr) => new PullRequest($this->connector, $this->owner, $this->repo, PullRequestData::fromArray($pr)),
            $response
        );
    }

    /**
     * Get only open pull requests
     */
    public function open(): array
    {
        return $this->list(['state' => 'open']);
    }

    /**
     * Get only closed pull requests
     */
    public function closed(): array
    {
        return $this->list(['state' => 'closed']);
    }

    /**
     * Create a new pull request (instance method)
     */
    public function createPullRequest(array $data): PullRequest
    {
        $response = $this->connector->post("/repos/{$this->owner}/{$this->repo}/pulls", $data);

        return new PullRequest($this->connector, $this->owner, $this->repo, PullRequestData::fromArray($response));
    }

    /**
     * Update a pull request
     */
    public function update(int $number, array $data): PullRequest
    {
        $response = $this->connector->patch("/repos/{$this->owner}/{$this->repo}/pulls/{$number}", $data);

        return new PullRequest($this->connector, $this->owner, $this->repo, PullRequestData::fromArray($response));
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

        $response = $this->connector->put("/repos/{$this->owner}/{$this->repo}/pulls/{$number}/merge", $data);

        return $response['merged'] ?? false;
    }

    /**
     * Close a pull request
     */
    public function close(int $number): PullRequest
    {
        return $this->update($number, ['state' => 'closed']);
    }
}
