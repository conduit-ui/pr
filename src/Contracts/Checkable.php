<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\CheckRun;

/**
 * Interface for entities that can have CI check runs.
 *
 * Implemented by: PullRequest (via head commit), Commit
 */
interface Checkable
{
    /**
     * Get all check runs for this entity.
     *
     * @return array<int, CheckRun>
     */
    public function checks(): array;
}
