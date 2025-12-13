<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\PullRequest;
use ConduitUI\Pr\QueryBuilder;

interface PrServiceInterface
{
    public function find(string $repository, int $number): PullRequest;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(string $repository, array $attributes): PullRequest;

    public function for(string $repository): QueryBuilder;

    public function query(): QueryBuilder;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $repository, int $number, array $data): PullRequest;

    public function merge(string $repository, int $number, ?string $commitMessage = null, ?string $mergeMethod = null): bool;

    public function close(string $repository, int $number): PullRequest;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, PullRequest>
     */
    public function list(string $repository, array $filters = []): array;
}
