<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Services;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\Contracts\ReviewBuilderInterface;
use ConduitUI\Pr\DataTransferObjects\Review;
use ConduitUI\Pr\Requests\CreatePullRequestReview;
use InvalidArgumentException;

final class ReviewBuilder implements ReviewBuilderInterface
{
    protected ?string $event = null;

    protected ?string $body = null;

    /**
     * @var array<int, array{path: string, line: int, body: string}|array{path: string, start_line: int, line: int, body: string}>
     */
    protected array $comments = [];

    protected string $owner;

    protected string $repo;

    public function __construct(
        protected Connector $connector,
        string $fullName,
        protected int $prNumber,
    ) {
        [$this->owner, $this->repo] = explode('/', $fullName, 2);
    }

    public function approve(?string $comment = null): self
    {
        $this->event = 'APPROVE';
        $this->body = $comment;

        return $this;
    }

    public function requestChanges(?string $comment = null): self
    {
        $this->event = 'REQUEST_CHANGES';
        $this->body = $comment ?? 'Changes requested';

        return $this;
    }

    public function comment(string $body): self
    {
        $this->event = 'COMMENT';
        $this->body = $body;

        return $this;
    }

    public function addInlineComment(string $path, int $line, string $comment): self
    {
        $this->comments[] = [
            'path' => $path,
            'line' => $line,
            'body' => $comment,
        ];

        return $this;
    }

    public function addSuggestion(string $path, int $startLine, int $endLine, string $suggestion): self
    {
        $this->comments[] = [
            'path' => $path,
            'start_line' => $startLine,
            'line' => $endLine,
            'body' => "```suggestion\n{$suggestion}\n```",
        ];

        return $this;
    }

    public function submit(): Review
    {
        if ($this->event === null) {
            throw new InvalidArgumentException('Review event is required. Call approve(), requestChanges(), or comment() first.');
        }

        $response = $this->connector->send(new CreatePullRequestReview(
            $this->owner,
            $this->repo,
            $this->prNumber,
            $this->event,
            $this->body,
            $this->comments
        ));

        /** @var array{id: int, user: array{id: int, login: string, avatar_url: string, html_url: string, type: string}, body?: string|null, state: string, html_url: string, submitted_at: string} $data */
        $data = $response->json();

        return Review::fromArray($data);
    }
}
