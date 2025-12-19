<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\PullRequest;

interface PullRequestQueryInterface
{
    /**
     * Set the repository context
     */
    public function repository(string $repository): self;

    /**
     * Filter by state
     */
    public function state(string $state): self;

    /**
     * Filter for open pull requests
     */
    public function open(): self;

    /**
     * Filter for closed pull requests
     */
    public function closed(): self;

    /**
     * Filter for all pull requests (open and closed)
     */
    public function all(): self;

    /**
     * Filter for merged pull requests
     */
    public function whereMerged(): self;

    /**
     * Filter for draft pull requests
     */
    public function whereDraft(): self;

    /**
     * Filter by base branch
     */
    public function whereBase(string $branch): self;

    /**
     * Filter by head branch
     */
    public function whereHead(string $branch): self;

    /**
     * Filter by author username
     */
    public function author(string $author): self;

    /**
     * Filter by label
     */
    public function label(string $label): self;

    /**
     * Order by a specific field
     */
    public function orderBy(string $sort, string $direction = 'desc'): self;

    /**
     * Limit the number of results
     */
    public function take(int $limit): self;

    /**
     * Set the page number for pagination
     */
    public function page(int $page): self;

    /**
     * Execute the query and get results
     *
     * @return array<int, PullRequest>
     */
    public function get(): array;

    /**
     * Get the first result or null
     */
    public function first(): ?PullRequest;

    /**
     * Count the results
     */
    public function count(): int;
}
