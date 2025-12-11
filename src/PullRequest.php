<?php

declare(strict_types=1);

namespace ConduitUI\Prs;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Prs\DataTransferObjects\Comment;
use ConduitUI\Prs\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Prs\DataTransferObjects\Review;

class PullRequest
{
    public function __construct(
        protected Connector $connector,
        protected string $owner,
        protected string $repo,
        public readonly PullRequestData $data,
    ) {
    }

    public function approve(?string $body = null): self
    {
        $this->createReview('APPROVE', $body);

        return $this;
    }

    public function requestChanges(string $body): self
    {
        $this->createReview('REQUEST_CHANGES', $body);

        return $this;
    }

    public function comment(string $body, ?int $line = null, ?string $path = null): self
    {
        if ($line !== null && $path !== null) {
            $this->createReviewComment($body, $path, $line);
        } else {
            $this->createIssueComment($body);
        }

        return $this;
    }

    public function merge(string $method = 'merge', ?string $title = null, ?string $message = null): self
    {
        $payload = [
            'merge_method' => $method,
        ];

        if ($title !== null) {
            $payload['commit_title'] = $title;
        }

        if ($message !== null) {
            $payload['commit_message'] = $message;
        }

        $this->connector->put(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}/merge",
            $payload
        );

        return $this;
    }

    public function close(): self
    {
        $this->connector->patch(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}",
            ['state' => 'closed']
        );

        return $this;
    }

    public function reopen(): self
    {
        $this->connector->patch(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}",
            ['state' => 'open']
        );

        return $this;
    }

    public function update(array $attributes): self
    {
        $this->connector->patch(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}",
            $attributes
        );

        return $this;
    }

    public function reviews(): array
    {
        $response = $this->connector->get(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}/reviews"
        );

        return array_map(
            fn (array $data) => Review::fromArray($data),
            $response
        );
    }

    public function comments(): array
    {
        $response = $this->connector->get(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}/comments"
        );

        return array_map(
            fn (array $data) => Comment::fromArray($data),
            $response
        );
    }

    public function files(): array
    {
        $response = $this->connector->get(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}/files"
        );

        return $response;
    }

    public function checks(): array
    {
        $response = $this->connector->get(
            "/repos/{$this->owner}/{$this->repo}/commits/{$this->data->head->sha}/check-runs"
        );

        return $response['check_runs'] ?? [];
    }

    public function addLabels(array $labels): self
    {
        $this->connector->post(
            "/repos/{$this->owner}/{$this->repo}/issues/{$this->data->number}/labels",
            ['labels' => $labels]
        );

        return $this;
    }

    public function removeLabel(string $label): self
    {
        $this->connector->delete(
            "/repos/{$this->owner}/{$this->repo}/issues/{$this->data->number}/labels/{$label}"
        );

        return $this;
    }

    public function addReviewers(array $reviewers, array $teamReviewers = []): self
    {
        $payload = [];

        if (! empty($reviewers)) {
            $payload['reviewers'] = $reviewers;
        }

        if (! empty($teamReviewers)) {
            $payload['team_reviewers'] = $teamReviewers;
        }

        $this->connector->post(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}/requested_reviewers",
            $payload
        );

        return $this;
    }

    public function removeReviewers(array $reviewers, array $teamReviewers = []): self
    {
        $payload = [];

        if (! empty($reviewers)) {
            $payload['reviewers'] = $reviewers;
        }

        if (! empty($teamReviewers)) {
            $payload['team_reviewers'] = $teamReviewers;
        }

        $this->connector->delete(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}/requested_reviewers",
            $payload
        );

        return $this;
    }

    protected function createReview(string $event, ?string $body = null): void
    {
        $payload = ['event' => $event];

        if ($body !== null) {
            $payload['body'] = $body;
        }

        $this->connector->post(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}/reviews",
            $payload
        );
    }

    protected function createReviewComment(string $body, string $path, int $line): void
    {
        $this->connector->post(
            "/repos/{$this->owner}/{$this->repo}/pulls/{$this->data->number}/comments",
            [
                'body' => $body,
                'path' => $path,
                'line' => $line,
            ]
        );
    }

    protected function createIssueComment(string $body): void
    {
        $this->connector->post(
            "/repos/{$this->owner}/{$this->repo}/issues/{$this->data->number}/comments",
            ['body' => $body]
        );
    }

    public function __get(string $name): mixed
    {
        return $this->data->{$name};
    }

    public function toArray(): array
    {
        return $this->data->toArray();
    }
}
