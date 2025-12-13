<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

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
    ) {}

    /**
     * @param  array{number: int, title: string, body?: string|null, state: string, user: array{id: int, login: string, avatar_url: string, html_url: string, type: string}, html_url: string, created_at: string, updated_at: string, closed_at?: string|null, merged_at?: string|null, merge_commit_sha?: string|null, draft?: bool, additions?: int|null, deletions?: int|null, changed_files?: int|null, assignee?: array{id: int, login: string, avatar_url: string, html_url: string, type: string}|null, assignees?: array<int, array{id: int, login: string, avatar_url: string, html_url: string, type: string}>, requested_reviewers?: array<int, array{id: int, login: string, avatar_url: string, html_url: string, type: string}>, labels?: array<int, array{id: int, name: string, color: string, description?: string|null}>, head: array{ref: string, sha: string, user: array{id: int, login: string, avatar_url: string, html_url: string, type: string}, repo: array{id: int, name: string, full_name: string, html_url: string, private: bool}}, base: array{ref: string, sha: string, user: array{id: int, login: string, avatar_url: string, html_url: string, type: string}, repo: array{id: int, name: string, full_name: string, html_url: string, private: bool}}}  $data
     */
    public static function fromArray(array $data): self
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
