<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\Commit;

/**
 * Interface for entities that have commits.
 *
 * Implemented by: PullRequest
 */
interface HasCommits
{
    /**
     * Get all commits for this entity.
     *
     * @return array<int, Commit>
     */
    public function commits(): array;
}
