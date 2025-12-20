<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

use Carbon\Carbon;

class Milestone
{
    public function __construct(
        public readonly int $number,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $state,
        public readonly int $openIssues,
        public readonly int $closedIssues,
        public readonly ?Carbon $dueOn,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt,
        public readonly ?Carbon $closedAt,
        public readonly string $htmlUrl,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            number: $data['number'],
            title: $data['title'],
            description: $data['description'] ?? null,
            state: $data['state'],
            openIssues: $data['open_issues'],
            closedIssues: $data['closed_issues'],
            dueOn: isset($data['due_on']) ? Carbon::parse($data['due_on']) : null,
            createdAt: Carbon::parse($data['created_at']),
            updatedAt: Carbon::parse($data['updated_at']),
            closedAt: isset($data['closed_at']) ? Carbon::parse($data['closed_at']) : null,
            htmlUrl: $data['html_url'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'title' => $this->title,
            'description' => $this->description,
            'state' => $this->state,
            'open_issues' => $this->openIssues,
            'closed_issues' => $this->closedIssues,
            'due_on' => $this->dueOn?->toIso8601String(),
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
            'closed_at' => $this->closedAt?->toIso8601String(),
            'html_url' => $this->htmlUrl,
        ];
    }

    public function isOpen(): bool
    {
        return $this->state === 'open';
    }

    public function isClosed(): bool
    {
        return $this->state === 'closed';
    }

    public function isOverdue(): bool
    {
        return $this->dueOn !== null
            && $this->dueOn->isPast()
            && $this->isOpen();
    }

    public function progress(): float
    {
        $total = $this->openIssues + $this->closedIssues;

        if ($total === 0) {
            return 0.0;
        }

        return round(($this->closedIssues / $total) * 100, 2);
    }
}
