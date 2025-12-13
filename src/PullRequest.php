<?php

declare(strict_types=1);

namespace ConduitUI\Pr;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\Contracts\Assignable;
use ConduitUI\Pr\Contracts\Auditable;
use ConduitUI\Pr\Contracts\Checkable;
use ConduitUI\Pr\Contracts\Closeable;
use ConduitUI\Pr\Contracts\Commentable;
use ConduitUI\Pr\Contracts\Diffable;
use ConduitUI\Pr\Contracts\HasCommits;
use ConduitUI\Pr\Contracts\Labelable;
use ConduitUI\Pr\Contracts\Mergeable;
use ConduitUI\Pr\Contracts\Reviewable;
use ConduitUI\Pr\DataTransferObjects\CheckRun;
use ConduitUI\Pr\DataTransferObjects\Comment;
use ConduitUI\Pr\DataTransferObjects\Commit;
use ConduitUI\Pr\DataTransferObjects\File;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\DataTransferObjects\Review;
use ConduitUI\Pr\Requests\AddAssignees;
use ConduitUI\Pr\Requests\AddIssueLabels;
use ConduitUI\Pr\Requests\CreateIssueComment;
use ConduitUI\Pr\Requests\CreatePullRequestComment;
use ConduitUI\Pr\Requests\CreatePullRequestReview;
use ConduitUI\Pr\Requests\GetCommitCheckRuns;
use ConduitUI\Pr\Requests\GetIssueComments;
use ConduitUI\Pr\Requests\GetIssueTimeline;
use ConduitUI\Pr\Requests\GetPullRequestComments;
use ConduitUI\Pr\Requests\GetPullRequestCommits;
use ConduitUI\Pr\Requests\GetPullRequestDiff;
use ConduitUI\Pr\Requests\GetPullRequestFiles;
use ConduitUI\Pr\Requests\GetPullRequestReviews;
use ConduitUI\Pr\Requests\MergePullRequest;
use ConduitUI\Pr\Requests\RemoveAssignees;
use ConduitUI\Pr\Requests\RemoveIssueLabel;
use ConduitUI\Pr\Requests\RemoveReviewers;
use ConduitUI\Pr\Requests\RequestReviewers;
use ConduitUI\Pr\Requests\UpdatePullRequest;

class PullRequest implements Assignable, Auditable, Checkable, Closeable, Commentable, Diffable, HasCommits, Labelable, Mergeable, Reviewable
{
    public function __construct(
        protected Connector $connector,
        protected string $owner,
        protected string $repo,
        public readonly PullRequestData $data,
    ) {}

    public function approve(?string $body = null): static
    {
        return $this->submitReview('APPROVE', $body);
    }

    public function requestChanges(string $body): static
    {
        return $this->submitReview('REQUEST_CHANGES', $body);
    }

    /**
     * Submit a review with optional inline comments.
     *
     * @param  array<int, array{path: string, line: int, body: string}>  $comments
     */
    public function submitReview(string $event, ?string $body = null, array $comments = []): static
    {
        $this->connector->send(new CreatePullRequestReview(
            $this->owner,
            $this->repo,
            $this->data->number,
            $event,
            $body,
            $comments
        ));

        return $this;
    }

    public function comment(string $body, ?int $line = null, ?string $path = null): static
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

    public function merge(string $method = 'merge', ?string $title = null, ?string $message = null): static
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

    public function close(): static
    {
        $this->connector->send(new UpdatePullRequest(
            $this->owner,
            $this->repo,
            $this->data->number,
            ['state' => 'closed']
        ));

        return $this;
    }

    public function reopen(): static
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
    public function update(array $attributes): static
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

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return array_values(array_map(
            /** @param array<string, mixed> $data */
            fn (mixed $data): Review => Review::fromArray($data), // @phpstan-ignore-line
            $items
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

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return array_values(array_map(
            /** @param array<string, mixed> $data */
            fn (mixed $data): Comment => Comment::fromArray($data), // @phpstan-ignore-line
            $items
        ));
    }

    /**
     * @return array<int, File>
     */
    public function files(): array
    {
        $response = $this->connector->send(new GetPullRequestFiles(
            $this->owner,
            $this->repo,
            $this->data->number
        ));

        /** @var array<int, array<string, mixed>> $items */
        $items = $response->json();

        return array_values(array_map(
            /** @param array<string, mixed> $data */
            fn (mixed $data): File => File::fromArray($data), // @phpstan-ignore-line
            $items
        ));
    }

    /**
/**
     * Get the raw diff text for this pull request.
     */
    public function diff(): string
    {
        $response = $this->connector->send(new GetPullRequestDiff(
            $this->owner,
            $this->repo,
            $this->data->number
        ));

        return $response->body();
    }

    /**
     * @return array<int, CheckRun>
     */
    public function checks(): array
    {
        $response = $this->connector->send(new GetCommitCheckRuns(
            $this->owner,
            $this->repo,
            $this->data->head->sha
        ));

        /** @var array<string, mixed> $json */
        $json = $response->json();
        /** @var array<int, array<string, mixed>> $checkRuns */
        $checkRuns = $json['check_runs'] ?? [];

        return array_values(array_map(
            /** @param array<string, mixed> $data */
            fn (mixed $data): CheckRun => CheckRun::fromArray($data), // @phpstan-ignore-line
            $checkRuns
        ));
    }

    /**
     * Get all commits in this pull request (paginated, fetches all pages).
     *
     * @return array<int, Commit>
     */
    public function commits(): array
    {
        /** @var array<int, array<string, mixed>> $allCommits */
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

            /** @var array<int, array<string, mixed>> $commits */
            $commits = $response->json();

            if ($commits === []) {
                break;
            }

            $allCommits = array_merge($allCommits, $commits);
            $page++;
        } while (count($commits) === $perPage);

        return array_values(array_map(
            /** @param array<string, mixed> $data */
            fn (mixed $data): Commit => Commit::fromArray($data), // @phpstan-ignore-line
            $allCommits
        ));
    }

    /**
     * Get all issue comments (discussion thread) for this pull request (paginated, fetches all pages).
     *
     * @return array<int, Comment>
     */
    public function issueComments(): array
    {
        /** @var array<int, array<string, mixed>> $allComments */
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

            /** @var array<int, array<string, mixed>> $comments */
            $comments = $response->json();

            if ($comments === []) {
                break;
            }

            $allComments = array_merge($allComments, $comments);
            $page++;
        } while (count($comments) === $perPage);

        return array_values(array_map(
            /** @param array<string, mixed> $data */
            fn (mixed $data): Comment => Comment::fromArray($data), // @phpstan-ignore-line
            $allComments
        ));
    }

    /**
     * @param  array<int, string>  $labels
     */
    public function addLabels(array $labels): static
    {
        $this->connector->send(new AddIssueLabels(
            $this->owner,
            $this->repo,
            $this->data->number,
            $labels
        ));

        return $this;
    }

    public function removeLabel(string $label): static
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
    public function addReviewers(array $reviewers, array $teamReviewers = []): static
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
    public function removeReviewers(array $reviewers, array $teamReviewers = []): static
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

    /**
     * @param  array<int, string>  $assignees
     */
    public function assign(array $assignees): static
    {
        $this->connector->send(new AddAssignees(
            $this->owner,
            $this->repo,
            $this->data->number,
            $assignees
        ));

        return $this;
    }

    /**
     * @param  array<int, string>  $assignees
     */
    public function unassign(array $assignees): static
    {
        $this->connector->send(new RemoveAssignees(
            $this->owner,
            $this->repo,
            $this->data->number,
            $assignees
        ));

        return $this;
    }

    /**
     * Get the timeline of events for this pull request (paginated, fetches all pages).
     *
     * @return array<int, mixed>
     */
    public function timeline(): array
    {
        $allEvents = [];
        $page = 1;
        $perPage = 100;

        do {
            $response = $this->connector->send(new GetIssueTimeline(
                $this->owner,
                $this->repo,
                $this->data->number,
                $perPage,
                $page
            ));

            $events = $response->json();

            if ($events === []) {
                break;
            }

            $allEvents = array_merge($allEvents, $events);
            $page++;
        } while (count($events) === $perPage);

        return $allEvents;
    }

    public function __get(string $name): mixed
    {
        return $this->data->{$name}; // @phpstan-ignore-line Variable property access is intentional for magic getter
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data->toArray();
    }
}
