<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

use DateTimeImmutable;

class Comment
{
    public function __construct(
        public readonly int $id,
        public readonly User $user,
        public readonly string $body,
        public readonly string $htmlUrl,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            user: User::fromArray($data['user']),
            body: $data['body'],
            htmlUrl: $data['html_url'],
            createdAt: new DateTimeImmutable($data['created_at']),
            updatedAt: new DateTimeImmutable($data['updated_at']),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user->toArray(),
            'body' => $this->body,
            'html_url' => $this->htmlUrl,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt->format('c'),
        ];
    }
}
