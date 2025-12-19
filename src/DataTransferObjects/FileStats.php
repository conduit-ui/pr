<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

class FileStats
{
    public function __construct(
        public readonly int $total,
        public readonly int $added,
        public readonly int $modified,
        public readonly int $removed,
        public readonly int $renamed,
        public readonly int $totalAdditions,
        public readonly int $totalDeletions,
        public readonly int $totalChanges,
    ) {}

    /**
     * @param  array{total: int, added: int, modified: int, removed: int, renamed: int, total_additions: int, total_deletions: int, total_changes: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total'],
            added: $data['added'],
            modified: $data['modified'],
            removed: $data['removed'],
            renamed: $data['renamed'],
            totalAdditions: $data['total_additions'],
            totalDeletions: $data['total_deletions'],
            totalChanges: $data['total_changes'],
        );
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'added' => $this->added,
            'modified' => $this->modified,
            'removed' => $this->removed,
            'renamed' => $this->renamed,
            'total_additions' => $this->totalAdditions,
            'total_deletions' => $this->totalDeletions,
            'total_changes' => $this->totalChanges,
        ];
    }
}
