<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\PullRequest;

/**
 * Contract for managing pull requests
 */
interface PullRequestManagerInterface
{
    public function find(int $number): PullRequest;

    public function get(int $number): PullRequest;

    public function query(): PullRequestQueryInterface;

    public function create(): PullRequestBuilderInterface;
}
