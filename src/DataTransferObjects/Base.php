<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

class Base
{
    public function __construct(
        public readonly string $ref,
        public readonly string $sha,
        public readonly User $user,
        public readonly Repository $repo,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ref: $data['ref'],
            sha: $data['sha'],
            user: User::fromArray($data['user']),
            repo: Repository::fromArray($data['repo']),
        );
    }

    public function toArray(): array
    {
        return [
            'ref' => $this->ref,
            'sha' => $this->sha,
            'user' => $this->user->toArray(),
            'repo' => $this->repo->toArray(),
        ];
    }
}
