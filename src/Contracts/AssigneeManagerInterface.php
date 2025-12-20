<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use Illuminate\Support\Collection;

interface AssigneeManagerInterface
{
    /**
     * Get all assignees for the pull request.
     *
     * @return Collection<int, \ConduitUI\Pr\DataTransferObjects\User>
     */
    public function get(): Collection;

    /**
     * Add a single assignee.
     */
    public function add(string $username): self;

    /**
     * Add multiple assignees.
     *
     * @param  array<int, string>  $usernames
     */
    public function addMany(array $usernames): self;

    /**
     * Remove a single assignee.
     */
    public function remove(string $username): self;

    /**
     * Remove multiple assignees.
     *
     * @param  array<int, string>  $usernames
     */
    public function removeMany(array $usernames): self;

    /**
     * Replace all assignees with new ones.
     *
     * @param  array<int, string>  $usernames
     */
    public function replace(array $usernames): self;

    /**
     * Clear all assignees.
     */
    public function clear(): self;

    /**
     * Check if a user is assigned.
     */
    public function has(string $username): bool;
}
