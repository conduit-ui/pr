<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\Review;

/**
 * Interface for entities that can receive code reviews.
 *
 * Implemented by: PullRequest
 */
interface Reviewable
{
    /**
     * Get all reviews for this entity.
     *
     * @return array<int, Review>
     */
    public function reviews(): array;

    /**
     * Approve this entity.
     */
    public function approve(?string $body = null): static;

    /**
     * Request changes on this entity.
     */
    public function requestChanges(string $body): static;

    /**
     * Submit a review with a specific event type.
     *
     * @param  string  $event  APPROVE, REQUEST_CHANGES, or COMMENT
     * @param  array<int, array{path: string, line: int, body: string}>  $comments  Inline comments
     */
    public function submitReview(string $event, ?string $body = null, array $comments = []): static;
}
