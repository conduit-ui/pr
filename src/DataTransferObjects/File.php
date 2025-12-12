<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

class File
{
    public function __construct(
        public readonly string $sha,
        public readonly string $filename,
        public readonly string $status,
        public readonly int $additions,
        public readonly int $deletions,
        public readonly int $changes,
        public readonly string $blobUrl,
        public readonly string $rawUrl,
        public readonly string $contentsUrl,
        public readonly ?string $patch,
        public readonly ?string $previousFilename,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sha: $data['sha'],
            filename: $data['filename'],
            status: $data['status'],
            additions: $data['additions'],
            deletions: $data['deletions'],
            changes: $data['changes'],
            blobUrl: $data['blob_url'],
            rawUrl: $data['raw_url'],
            contentsUrl: $data['contents_url'],
            patch: $data['patch'] ?? null,
            previousFilename: $data['previous_filename'] ?? null,
        );
    }

    public function isAdded(): bool
    {
        return $this->status === 'added';
    }

    public function isRemoved(): bool
    {
        return $this->status === 'removed';
    }

    public function isModified(): bool
    {
        return $this->status === 'modified';
    }

    public function isRenamed(): bool
    {
        return $this->status === 'renamed';
    }

    public function toArray(): array
    {
        return [
            'sha' => $this->sha,
            'filename' => $this->filename,
            'status' => $this->status,
            'additions' => $this->additions,
            'deletions' => $this->deletions,
            'changes' => $this->changes,
            'blob_url' => $this->blobUrl,
            'raw_url' => $this->rawUrl,
            'contents_url' => $this->contentsUrl,
            'patch' => $this->patch,
            'previous_filename' => $this->previousFilename,
        ];
    }
}
