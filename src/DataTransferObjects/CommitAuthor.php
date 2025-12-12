<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

use DateTimeImmutable;

class CommitAuthor
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly DateTimeImmutable $date,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            date: new DateTimeImmutable($data['date']),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'date' => $this->date->format('c'),
        ];
    }
}
