<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\Review;
use ConduitUI\Pr\Requests\GetPullRequestReviews;

final class ReviewQuery
{
    protected string $owner;

    protected string $repo;

    public function __construct(
        protected Connector $connector,
        string $fullName,
        protected int $prNumber,
    ) {
        [$this->owner, $this->repo] = explode('/', $fullName, 2);
    }

    /**
     * Get all reviews for the pull request.
     *
     * @return array<int, Review>
     */
    public function get(): array
    {
        $response = $this->connector->send(new GetPullRequestReviews(
            $this->owner,
            $this->repo,
            $this->prNumber
        ));

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return array_values(array_map(
            /** @param array<string, mixed> $data */
            fn (mixed $data): Review => Review::fromArray($data), // @phpstan-ignore-line
            $items
        ));
    }

    /**
     * Get only approved reviews.
     *
     * @return array<int, Review>
     */
    public function whereApproved(): array
    {
        return array_values(array_filter(
            $this->get(),
            fn (Review $review): bool => $review->isApproved()
        ));
    }

    /**
     * Get only reviews with changes requested.
     *
     * @return array<int, Review>
     */
    public function whereChangesRequested(): array
    {
        return array_values(array_filter(
            $this->get(),
            fn (Review $review): bool => $review->isChangesRequested()
        ));
    }

    /**
     * Get only comment reviews.
     *
     * @return array<int, Review>
     */
    public function whereCommented(): array
    {
        return array_values(array_filter(
            $this->get(),
            fn (Review $review): bool => $review->isCommented()
        ));
    }

    /**
     * Get reviews by a specific user.
     *
     * @return array<int, Review>
     */
    public function byUser(string $username): array
    {
        return array_values(array_filter(
            $this->get(),
            fn (Review $review): bool => $review->user->login === $username
        ));
    }

    /**
     * Get the latest review.
     */
    public function latest(): ?Review
    {
        $reviews = $this->get();

        if ($reviews === []) {
            return null;
        }

        usort($reviews, fn (Review $a, Review $b): int => $b->submittedAt <=> $a->submittedAt);

        return $reviews[0];
    }

    /**
     * Get the first review.
     */
    public function first(): ?Review
    {
        $reviews = $this->get();

        return $reviews[0] ?? null;
    }

    /**
     * Count the number of reviews.
     */
    public function count(): int
    {
        return count($this->get());
    }
}
