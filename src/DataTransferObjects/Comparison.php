<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

class Comparison
{
    public function __construct(
        public readonly int $aheadBy,
        public readonly int $behindBy,
        public readonly string $status,
    ) {}

    /**
     * @param  array{ahead_by: int, behind_by: int, status: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            aheadBy: $data['ahead_by'],
            behindBy: $data['behind_by'],
            status: $data['status'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ahead_by' => $this->aheadBy,
            'behind_by' => $this->behindBy,
            'status' => $this->status,
        ];
    }
}
