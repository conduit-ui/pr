<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\PullRequest;

/**
 * Contract for merge operations
 */
interface MergeManagerInterface
{
    public function merge(?string $commitTitle = null, ?string $commitMessage = null): PullRequest;

    public function squash(?string $commitTitle = null, ?string $commitMessage = null): PullRequest;

    public function rebase(): PullRequest;

    public function canMerge(): bool;

    public function deleteBranch(): bool;
}
