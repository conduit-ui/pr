<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

/**
 * Interface for entities that can be opened and closed.
 *
 * Implemented by: Issue, PullRequest
 */
interface Closeable
{
    /**
     * Close this entity.
     */
    public function close(): static;

    /**
     * Reopen this entity.
     */
    public function reopen(): static;
}
