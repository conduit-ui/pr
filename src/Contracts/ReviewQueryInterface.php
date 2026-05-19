<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\Review;
use Illuminate\Support\Collection;

/**
 * Contract for querying reviews
 */
interface ReviewQueryInterface
{
    /**
     * @return Collection<int, Review>
     */
    public function get(): Collection;

    /**
     * @return Collection<int, Review>
     */
    public function whereApproved(): Collection;

    /**
     * @return Collection<int, Review>
     */
    public function whereChangesRequested(): Collection;

    /**
     * @return Collection<int, Review>
     */
    public function wherePending(): Collection;

    /**
     * @return Collection<int, Review>
     */
    public function byUser(string $username): Collection;

    public function latest(): ?Review;
}
