<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

/**
 * Interface for entities that can have labels.
 *
 * Implemented by: Issue, PullRequest
 */
interface Labelable
{
    /**
     * Add labels to this entity.
     *
     * @param  array<int, string>  $labels
     */
    public function addLabels(array $labels): static;

    /**
     * Remove a label from this entity.
     */
    public function removeLabel(string $label): static;
}
