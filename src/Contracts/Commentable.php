<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\Comment;

/**
 * Interface for entities that can have comments.
 *
 * Implemented by: Issue, PullRequest, Commit, Review
 */
interface Commentable
{
    /**
     * Get all comments on this entity.
     *
     * @return array<int, Comment>
     */
    public function comments(): array;

    /**
     * Add a comment to this entity.
     */
    public function comment(string $body): static;
}
