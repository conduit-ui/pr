<?php

declare(strict_types=1);

namespace ConduitUI\Pr\DataTransferObjects;

class Commit
{
    public function __construct(
        public readonly string $sha,
        public readonly string $message,
        public readonly CommitAuthor $author,
        public readonly CommitAuthor $committer,
        public readonly string $htmlUrl,
        public readonly ?User $githubAuthor,
        public readonly ?User $githubCommitter,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sha: $data['sha'],
            message: $data['commit']['message'],
            author: CommitAuthor::fromArray($data['commit']['author']),
            committer: CommitAuthor::fromArray($data['commit']['committer']),
            htmlUrl: $data['html_url'],
            githubAuthor: isset($data['author']) ? User::fromArray($data['author']) : null,
            githubCommitter: isset($data['committer']) ? User::fromArray($data['committer']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'sha' => $this->sha,
            'commit' => [
                'message' => $this->message,
                'author' => $this->author->toArray(),
                'committer' => $this->committer->toArray(),
            ],
            'html_url' => $this->htmlUrl,
            'author' => $this->githubAuthor?->toArray(),
            'committer' => $this->githubCommitter?->toArray(),
        ];
    }
}
