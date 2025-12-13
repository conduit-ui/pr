<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\File;

/**
 * Interface for entities that have a diff (changed files).
 *
 * Implemented by: PullRequest, Commit
 */
interface Diffable
{
    /**
     * Get the raw diff text.
     */
    public function diff(): string;

    /**
     * Get the list of changed files.
     *
     * @return array<int, File>
     */
    public function files(): array;
}
