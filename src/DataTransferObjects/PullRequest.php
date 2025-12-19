<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\Requests\AddAssignees;
use ConduitUI\Pr\Requests\AddIssueLabels;
use ConduitUI\Pr\Requests\CreateIssueComment;
use ConduitUI\Pr\Requests\CreatePullRequestReview;
use ConduitUI\Pr\Requests\MergePullRequest;
use ConduitUI\Pr\Requests\RemoveAssignees;
use ConduitUI\Pr\Requests\RemoveIssueLabel;
use ConduitUI\Pr\Requests\RequestReviewers;
use ConduitUI\Pr\Requests\UpdatePullRequest;
use DateTimeImmutable;

class PullRequest
{
    /**
     * @param  array<int, User>  $assignees
     * @param  array<int, User>  $requestedReviewers
     * @param  array<int, Label>  $labels
     */
    public function __construct(
        public readonly int $number,
        public readonly string $title,
        public readonly ?string $body,
        public readonly string $state,
        public readonly User $user,
        public readonly string $htmlUrl,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?DateTimeImmutable $closedAt,
        public readonly ?DateTimeImmutable $mergedAt,
        public readonly ?string $mergeCommitSha,
        public readonly bool $draft,
        public readonly ?int $additions,
        public readonly ?int $deletions,
        public readonly ?int $changedFiles,
        public readonly ?User $assignee,
        public readonly array $assignees,
        public readonly array $requestedReviewers,
        public readonly array $labels,
        public readonly Head $head,
        public readonly Base $base,
        private readonly ?Connector $connector = null,
        private readonly ?string $owner = null,
        private readonly ?string $repo = null,
    ) {}

    /**
     * @param  array{number: int, title: string, body?: string|null, state: string, user: array{id: int, login: string, avatar_url: string, html_url: string, type: string}, html_url: string, created_at: string, updated_at: string, closed_at?: string|null, merged_at?: string|null, merge_commit_sha?: string|null, draft?: bool, additions?: int|null, deletions?: int|null, changed_files?: int|null, assignee?: array{id: int, login: string, avatar_url: string, html_url: string, type: string}|null, assignees?: array<int, array{id: int, login: string, avatar_url: string, html_url: string, type: string}>, requested_reviewers?: array<int, array{id: int, login: string, avatar_url: string, html_url: string, type: string}>, labels?: array<int, array{id: int, name: string, color: string, description?: string|null}>, head: array{ref: string, sha: string, user: array{id: int, login: string, avatar_url: string, html_url: string, type: string}, repo: array{id: int, name: string, full_name: string, html_url: string, private: bool}}, base: array{ref: string, sha: string, user: array{id: int, login: string, avatar_url: string, html_url: string, type: string}, repo: array{id: int, name: string, full_name: string, html_url: string, private: bool}}}  $data
     */
    public static function fromArray(array $data, ?Connector $connector = null, ?string $owner = null, ?string $repo = null): self
    {
        return new self(
            number: $data['number'],
            title: $data['title'],
            body: $data['body'] ?? null,
            state: $data['state'],
            user: User::fromArray($data['user']),
            htmlUrl: $data['html_url'],
            createdAt: new DateTimeImmutable($data['created_at']),
            updatedAt: new DateTimeImmutable($data['updated_at']),
            closedAt: isset($data['closed_at']) ? new DateTimeImmutable($data['closed_at']) : null,
            mergedAt: isset($data['merged_at']) ? new DateTimeImmutable($data['merged_at']) : null,
            mergeCommitSha: $data['merge_commit_sha'] ?? null,
            draft: $data['draft'] ?? false,
            additions: $data['additions'] ?? null,
            deletions: $data['deletions'] ?? null,
            changedFiles: $data['changed_files'] ?? null,
            assignee: isset($data['assignee']) ? User::fromArray($data['assignee']) : null,
            assignees: array_map(fn ($assignee) => User::fromArray($assignee), $data['assignees'] ?? []),
            requestedReviewers: array_map(fn ($reviewer) => User::fromArray($reviewer), $data['requested_reviewers'] ?? []),
            labels: array_map(fn ($label) => Label::fromArray($label), $data['labels'] ?? []),
            head: Head::fromArray($data['head']),
            base: Base::fromArray($data['base']),
            connector: $connector,
            owner: $owner,
            repo: $repo,
        );
    }

    public function isOpen(): bool
    {
        return $this->state === 'open';
    }

    public function isClosed(): bool
    {
        return $this->state === 'closed';
    }

    public function isMerged(): bool
    {
        return $this->mergedAt !== null;
    }

    public function isDraft(): bool
    {
        return $this->draft;
    }

    /**
     * Merge the pull request.
     */
    public function merge(string $method = 'merge', ?string $message = null, ?string $title = null): self
    {
        $this->ensureConnectorAvailable();

        $payload = ['merge_method' => $method];

        if ($title !== null) {
            $payload['commit_title'] = $title;
        }

        if ($message !== null) {
            $payload['commit_message'] = $message;
        }

        $this->connector->send(new MergePullRequest(
            $this->owner,
            $this->repo,
            $this->number,
            $payload
        ));

        return $this;
    }

    /**
     * Squash merge the pull request.
     */
    public function squashMerge(?string $message = null): self
    {
        return $this->merge('squash', $message);
    }

    /**
     * Rebase merge the pull request.
     */
    public function rebaseMerge(): self
    {
        return $this->merge('rebase');
    }

    /**
     * Close the pull request.
     */
    public function close(): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->number,
            ['state' => 'closed']
        ));

        return $this;
    }

    /**
     * Reopen the pull request.
     */
    public function reopen(): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->number,
            ['state' => 'open']
        ));

        return $this;
    }

    /**
     * Mark the pull request as draft.
     */
    public function markDraft(): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->number,
            ['draft' => true]
        ));

        return $this;
    }

    /**
     * Mark the pull request as ready for review.
     */
    public function markReady(): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->number,
            ['draft' => false]
        ));

        return $this;
    }

    /**
     * Approve the pull request.
     */
    public function approve(?string $body = null): self
    {
        return $this->submitReview('APPROVE', $body);
    }

    /**
     * Request changes on the pull request.
     */
    public function requestChanges(string $body): self
    {
        return $this->submitReview('REQUEST_CHANGES', $body);
    }

    /**
     * Submit a review on the pull request.
     *
     * @param  array<int, array{path: string, line: int, body: string}>  $comments
     */
    public function submitReview(string $event, ?string $body = null, array $comments = []): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new CreatePullRequestReview(
            $this->owner,
            $this->repo,
            $this->number,
            $event,
            $body,
            $comments
        ));

        return $this;
    }

    /**
     * Add a comment to the pull request.
     */
    public function comment(string $body): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new CreateIssueComment(
            $this->owner,
            $this->repo,
            $this->number,
            $body
        ));

        return $this;
    }

    /**
     * Add a single label to the pull request.
     */
    public function addLabel(string $label): self
    {
        return $this->addLabels([$label]);
    }

    /**
     * Add multiple labels to the pull request.
     *
     * @param  array<int, string>  $labels
     */
    public function addLabels(array $labels): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new AddIssueLabels(
            $this->owner,
            $this->repo,
            $this->number,
            $labels
        ));

        return $this;
    }

    /**
     * Remove a label from the pull request.
     */
    public function removeLabel(string $label): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new RemoveIssueLabel(
            $this->owner,
            $this->repo,
            $this->number,
            $label
        ));

        return $this;
    }

    /**
     * Set labels on the pull request (replaces all existing labels).
     *
     * @param  array<int, string>  $labels
     */
    public function setLabels(array $labels): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->number,
            ['labels' => $labels]
        ));

        return $this;
    }

    /**
     * Request a single reviewer.
     */
    public function requestReviewer(string $username): self
    {
        return $this->requestReviewers([$username]);
    }

    /**
     * Request multiple reviewers.
     *
     * @param  array<int, string>  $usernames
     */
    public function requestReviewers(array $usernames): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new RequestReviewers(
            $this->owner,
            $this->repo,
            $this->number,
            $usernames,
            []
        ));

        return $this;
    }

    /**
     * Request a team review.
     */
    public function requestTeamReview(string $teamSlug): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new RequestReviewers(
            $this->owner,
            $this->repo,
            $this->number,
            [],
            [$teamSlug]
        ));

        return $this;
    }

    /**
     * Assign a single user to the pull request.
     */
    public function assignUser(string $username): self
    {
        return $this->assign([$username]);
    }

    /**
     * Assign users to the pull request.
     *
     * @param  array<int, string>  $usernames
     */
    public function assign(array $usernames): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new AddAssignees(
            $this->owner,
            $this->repo,
            $this->number,
            $usernames
        ));

        return $this;
    }

    /**
     * Unassign users from the pull request.
     *
     * @param  array<int, string>  $usernames
     */
    public function unassign(array $usernames): self
    {
        $this->ensureConnectorAvailable();

        $this->connector->send(new RemoveAssignees(
            $this->owner,
            $this->repo,
            $this->number,
            $usernames
        ));

        return $this;
    }

    /**
     * Ensure connector is available for actions.
     *
     * @phpstan-assert !null $this->connector
     * @phpstan-assert !null $this->owner
     * @phpstan-assert !null $this->repo
     */
    private function ensureConnectorAvailable(): void
    {
        if ($this->connector === null || $this->owner === null || $this->repo === null) {
            throw new \RuntimeException(
                'Connector, owner, and repo must be provided to perform actions on the pull request. '
                .'Use PullRequest::fromArray($data, $connector, $owner, $repo) to enable actions.'
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'title' => $this->title,
            'body' => $this->body,
            'state' => $this->state,
            'user' => $this->user->toArray(),
            'html_url' => $this->htmlUrl,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt->format('c'),
            'closed_at' => $this->closedAt?->format('c'),
            'merged_at' => $this->mergedAt?->format('c'),
            'merge_commit_sha' => $this->mergeCommitSha,
            'draft' => $this->draft,
            'additions' => $this->additions,
            'deletions' => $this->deletions,
            'changed_files' => $this->changedFiles,
            'assignee' => $this->assignee?->toArray(),
            'assignees' => array_map(fn ($assignee) => $assignee->toArray(), $this->assignees),
            'requested_reviewers' => array_map(fn ($reviewer) => $reviewer->toArray(), $this->requestedReviewers),
            'labels' => array_map(fn ($label) => $label->toArray(), $this->labels),
            'head' => $this->head->toArray(),
            'base' => $this->base->toArray(),
        ];
    }
}
