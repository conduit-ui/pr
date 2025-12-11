<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $login,
        public readonly string $avatarUrl,
        public readonly string $htmlUrl,
        public readonly string $type,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            login: $data['login'],
            avatarUrl: $data['avatar_url'],
            htmlUrl: $data['html_url'],
            type: $data['type'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'avatar_url' => $this->avatarUrl,
            'html_url' => $this->htmlUrl,
            'type' => $this->type,
        ];
    }
}
