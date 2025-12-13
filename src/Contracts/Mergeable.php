<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

/**
 * Interface for entities that can be merged.
 *
 * Implemented by: PullRequest
 */
interface Mergeable
{
    /**
     * Merge this entity.
     *
     * @param  string  $method  merge, squash, or rebase
     */
    public function merge(string $method = 'merge', ?string $title = null, ?string $message = null): static;
}
