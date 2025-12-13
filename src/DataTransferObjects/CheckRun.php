<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

use DateTimeImmutable;

class CheckRun
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $status,
        public readonly ?string $conclusion,
        public readonly string $htmlUrl,
        public readonly DateTimeImmutable $startedAt,
        public readonly ?DateTimeImmutable $completedAt,
    ) {}

    /**
     * @param  array{id: int, name: string, status: string, conclusion: string|null, html_url: string, started_at: string, completed_at?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            status: $data['status'],
            conclusion: $data['conclusion'] ?? null,
            htmlUrl: $data['html_url'],
            startedAt: new DateTimeImmutable($data['started_at']),
            completedAt: isset($data['completed_at']) ? new DateTimeImmutable($data['completed_at']) : null,
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isSuccessful(): bool
    {
        return $this->conclusion === 'success';
    }

    public function isFailed(): bool
    {
        return in_array($this->conclusion, ['failure', 'timed_out', 'action_required'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'conclusion' => $this->conclusion,
            'html_url' => $this->htmlUrl,
            'started_at' => $this->startedAt->format('c'),
            'completed_at' => $this->completedAt?->format('c'),
        ];
    }
}
