<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

use DateTimeImmutable;

class Review
{
    public function __construct(
        public readonly int $id,
        public readonly User $user,
        public readonly ?string $body,
        public readonly string $state,
        public readonly string $htmlUrl,
        public readonly DateTimeImmutable $submittedAt,
    ) {}

    /**
     * @param  array{id: int, user: array{id: int, login: string, avatar_url: string, html_url: string, type: string}, body?: string|null, state: string, html_url: string, submitted_at: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            user: User::fromArray($data['user']),
            body: $data['body'] ?? null,
            state: $data['state'],
            htmlUrl: $data['html_url'],
            submittedAt: new DateTimeImmutable($data['submitted_at']),
        );
    }

    public function isApproved(): bool
    {
        return $this->state === 'APPROVED';
    }

    public function isChangesRequested(): bool
    {
        return $this->state === 'CHANGES_REQUESTED';
    }

    public function isCommented(): bool
    {
        return $this->state === 'COMMENTED';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user->toArray(),
            'body' => $this->body,
            'state' => $this->state,
            'html_url' => $this->htmlUrl,
            'submitted_at' => $this->submittedAt->format('c'),
        ];
    }
}
