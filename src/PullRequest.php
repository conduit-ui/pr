<?php

declare(strict_types=1);

namespace ConduitUI\Pr;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\Comment;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\DataTransferObjects\Review;
use ConduitUI\Pr\Requests\AddIssueLabels;
use ConduitUI\Pr\Requests\CreateIssueComment;
use ConduitUI\Pr\Requests\CreatePullRequestComment;
use ConduitUI\Pr\Requests\CreatePullRequestReview;
use ConduitUI\Pr\Requests\GetCommitCheckRuns;
use ConduitUI\Pr\Requests\GetIssueComments;
use ConduitUI\Pr\Requests\GetPullRequestComments;
use ConduitUI\Pr\Requests\GetPullRequestCommits;
use ConduitUI\Pr\Requests\GetPullRequestFiles;
use ConduitUI\Pr\Requests\GetPullRequestReviews;
use ConduitUI\Pr\Requests\MergePullRequest;
use ConduitUI\Pr\Requests\RemoveIssueLabel;
use ConduitUI\Pr\Requests\RemoveReviewers;
use ConduitUI\Pr\Requests\RequestReviewers;
use ConduitUI\Pr\Requests\UpdatePullRequest;

class PullRequest
{
    public function __construct(
        protected Connector $connector,
        protected string $owner,
        protected string $repo,
        public readonly PullRequestData $data,
    ) {}

    public function approve(?string $body = null): self
    {
        $this->connector->send(new CreatePullRequestReview(
            $this->owner,
            $this->repo,
            $this->data->number,
            'APPROVE',
            $body
        ));

        return $this;
    }

    public function requestChanges(string $body): self
    {
        $this->connector->send(new CreatePullRequestReview(
            $this->owner,
            $this->repo,
            $this->data->number,
            'REQUEST_CHANGES',
            $body
        ));

        return $this;
    }

    public function comment(string $body, ?int $line = null, ?string $path = null): self
    {
        if ($line !== null && $path !== null) {
            $this->connector->send(new CreatePullRequestComment(
                $this->owner,
                $this->repo,
                $this->data->number,
                $body,
                $path,
                $line
            ));
        } else {
            $this->connector->send(new CreateIssueComment(
                $this->owner,
                $this->repo,
                $this->data->number,
                $body
            ));
        }

        return $this;
    }

    public function merge(string $method = 'merge', ?string $title = null, ?string $message = null): self
    {
        $payload = ['merge_method' => $method];

        if ($title !== null) {
            $payload['commit_title'] = $title;
        }

        if ($message !== null) {
            $payload['commit_message'] = $message;
        }

        $this->connector->send(new MergePullRequest(
            $this->owner,
            $this->repo,
            $this->data->number,
            $payload
        ));

        return $this;
    }

    public function close(): self
    {
        $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->data->number,
            ['state' => 'closed']
        ));

        return $this;
    }

    public function reopen(): self
    {
        $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->data->number,
            ['state' => 'open']
        ));

        return $this;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(array $attributes): self
    {
        $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->data->number,
            $attributes
        ));

        return $this;
    }

    /**
     * @return array<int, Review>
     */
    public function reviews(): array
    {
        $response = $this->connector->send(new GetPullRequestReviews(
            $this->owner,
            $this->repo,
            $this->data->number
        ));

        return array_values(array_map(
            fn (array $data) => Review::fromArray($data),
            $response->json()
        ));
    }

    /**
     * @return array<int, Comment>
     */
    public function comments(): array
    {
        $response = $this->connector->send(new GetPullRequestComments(
            $this->owner,
            $this->repo,
            $this->data->number
        ));

        return array_values(array_map(
            fn (array $data) => Comment::fromArray($data),
            $response->json()
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function files(): array
    {
        $response = $this->connector->send(new GetPullRequestFiles(
            $this->owner,
            $this->repo,
            $this->data->number
        ));

        return array_values($response->json());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function checks(): array
    {
        $response = $this->connector->send(new GetCommitCheckRuns(
            $this->owner,
            $this->repo,
            $this->data->head->sha
        ));

        return $response->json()['check_runs'] ?? [];
    }

    /**
     * Get all commits in this pull request (paginated, fetches all pages).
     *
     * @return array<int, array<string, mixed>>
     */
    public function commits(): array
    {
        $allCommits = [];
        $page = 1;
        $perPage = 100;

        do {
            $response = $this->connector->send(new GetPullRequestCommits(
                $this->owner,
                $this->repo,
                $this->data->number,
                $perPage,
                $page
            ));

            $commits = $response->json();

            if (empty($commits)) {
                break;
            }

            $allCommits = array_merge($allCommits, $commits);
            $page++;
        } while (count($commits) === $perPage);

        return array_values($allCommits);
    }

    /**
     * Get all issue comments (discussion thread) for this pull request (paginated, fetches all pages).
     *
     * @return array<int, Comment>
     */
    public function issueComments(): array
    {
        $allComments = [];
        $page = 1;
        $perPage = 100;

        do {
            $response = $this->connector->send(new GetIssueComments(
                $this->owner,
                $this->repo,
                $this->data->number,
                $perPage,
                $page
            ));

            $comments = $response->json();

            if (empty($comments)) {
                break;
            }

            $allComments = array_merge($allComments, $comments);
            $page++;
        } while (count($comments) === $perPage);

        return array_values(array_map(
            fn (array $data) => Comment::fromArray($data),
            $allComments
        ));
    }

    /**
     * @param  array<int, string>  $labels
     */
    public function addLabels(array $labels): self
    {
        $this->connector->send(new AddIssueLabels(
            $this->owner,
            $this->repo,
            $this->data->number,
            $labels
        ));

        return $this;
    }

    public function removeLabel(string $label): self
    {
        $this->connector->send(new RemoveIssueLabel(
            $this->owner,
            $this->repo,
            $this->data->number,
            $label
        ));

        return $this;
    }

    /**
     * @param  array<int, string>  $reviewers
     * @param  array<int, string>  $teamReviewers
     */
    public function addReviewers(array $reviewers, array $teamReviewers = []): self
    {
        $this->connector->send(new RequestReviewers(
            $this->owner,
            $this->repo,
            $this->data->number,
            $reviewers,
            $teamReviewers
        ));

        return $this;
    }

    /**
     * @param  array<int, string>  $reviewers
     * @param  array<int, string>  $teamReviewers
     */
    public function removeReviewers(array $reviewers, array $teamReviewers = []): self
    {
        $this->connector->send(new RemoveReviewers(
            $this->owner,
            $this->repo,
            $this->data->number,
            $reviewers,
            $teamReviewers
        ));

        return $this;
    }

    public function __get(string $name): mixed
    {
        return $this->data->{$name};
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data->toArray();
    }
}
