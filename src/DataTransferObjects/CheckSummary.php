<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

class CheckSummary
{
    public function __construct(
        public readonly int $total,
        public readonly int $passing,
        public readonly int $failing,
        public readonly int $pending,
        public readonly int $neutral,
        public readonly int $skipped,
    ) {}

    /**
     * @param  array{total: int, passing: int, failing: int, pending: int, neutral: int, skipped: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total'],
            passing: $data['passing'],
            failing: $data['failing'],
            pending: $data['pending'],
            neutral: $data['neutral'],
            skipped: $data['skipped'],
        );
    }

    public function allPassing(): bool
    {
        return $this->total > 0 && $this->failing === 0 && $this->pending === 0;
    }

    public function hasFailures(): bool
    {
        return $this->failing > 0;
    }

    public function hasPending(): bool
    {
        return $this->pending > 0;
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'passing' => $this->passing,
            'failing' => $this->failing,
            'pending' => $this->pending,
            'neutral' => $this->neutral,
            'skipped' => $this->skipped,
        ];
    }
}
