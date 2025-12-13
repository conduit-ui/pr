<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

/**
 * Interface for entities that can have assignees.
 *
 * Implemented by: Issue, PullRequest
 */
interface Assignable
{
    /**
     * Assign users to this entity.
     *
     * @param  array<int, string>  $assignees  Usernames to assign
     */
    public function assign(array $assignees): static;

    /**
     * Remove assignees from this entity.
     *
     * @param  array<int, string>  $assignees  Usernames to unassign
     */
    public function unassign(array $assignees): static;
}
