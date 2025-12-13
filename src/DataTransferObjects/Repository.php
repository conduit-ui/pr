<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

class Repository
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $fullName,
        public readonly string $htmlUrl,
        public readonly bool $private,
    ) {}

    /**
     * @param  array{id: int, name: string, full_name: string, html_url: string, private: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            fullName: $data['full_name'],
            htmlUrl: $data['html_url'],
            private: $data['private'],
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
            'full_name' => $this->fullName,
            'html_url' => $this->htmlUrl,
            'private' => $this->private,
        ];
    }
}
