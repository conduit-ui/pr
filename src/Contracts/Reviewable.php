<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\Services\ReviewBuilder;
use ConduitUI\Pr\Services\ReviewQuery;

/**
 * Interface for entities that can receive code reviews.
 *
 * Implemented by: PullRequest
 */
interface Reviewable
{
    /**
     * Get a query builder for reviews.
     */
    public function reviews(): ReviewQuery;

    /**
     * Create a review builder for approving.
     */
    public function approve(?string $body = null): ReviewBuilder;

    /**
     * Create a review builder for requesting changes.
     */
    public function requestChanges(?string $body = null): ReviewBuilder;

    /**
     * Create a new review builder.
     */
    public function review(): ReviewBuilder;

    /**
     * Submit a review with a specific event type (legacy method).
     *
     * @param  string  $event  APPROVE, REQUEST_CHANGES, or COMMENT
     * @param  array<int, array{path: string, line: int, body: string}>  $comments  Inline comments
     */
    public function submitReview(string $event, ?string $body = null, array $comments = []): static;
}
