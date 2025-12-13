<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

class Label
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $color,
        public readonly ?string $description,
    ) {}

    /**
     * @param  array{id: int, name: string, color: string, description?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            color: $data['color'],
            description: $data['description'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
        ];
    }
}
