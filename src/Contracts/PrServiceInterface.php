<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\PullRequest;
use ConduitUI\Pr\QueryBuilder;

interface PrServiceInterface
{
    public function find(string $repository, int $number): PullRequest;

    public function create(string $repository, array $attributes): PullRequest;

    public function for(string $repository): QueryBuilder;

    public function query(): QueryBuilder;

    public function update(string $repository, int $number, array $data): PullRequest;

    public function merge(string $repository, int $number, ?string $commitMessage = null, ?string $mergeMethod = null): bool;

    public function close(string $repository, int $number): PullRequest;

    public function list(string $repository, array $filters = []): array;
}
