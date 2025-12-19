<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\PullRequest;

/**
 * Contract for PR state actions
 */
interface PullRequestActionsInterface
{
    public function close(?string $comment = null): PullRequest;

    public function reopen(): PullRequest;

    public function markDraft(): PullRequest;

    public function markReady(): PullRequest;

    public function addLabel(string $label): self;

    /**
     * @param  array<int, string>  $labels
     */
    public function addLabels(array $labels): self;

    public function removeLabel(string $label): self;

    /**
     * @param  array<int, string>  $labels
     */
    public function setLabels(array $labels): self;

    public function requestReview(string $username): self;

    /**
     * @param  array<int, string>  $usernames
     */
    public function requestReviews(array $usernames): self;

    public function requestTeamReview(string $teamSlug): self;

    public function assign(string $username): self;

    public function unassign(string $username): self;

    public function comment(string $body): PullRequest;
}
