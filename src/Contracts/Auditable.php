<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

/**
 * Interface for entities that have an audit trail / timeline.
 *
 * Implemented by: Issue, PullRequest
 */
interface Auditable
{
    /**
     * Get the timeline of events for this entity.
     *
     * @return array<int, mixed>
     */
    public function timeline(): array;
}
