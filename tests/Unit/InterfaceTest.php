<?php

declare(strict_types=1);

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
use ConduitUI\Pr\PullRequest;

it('implements Commentable interface', function () {
    expect(PullRequest::class)->toImplement(Commentable::class);
});

it('implements Labelable interface', function () {
    expect(PullRequest::class)->toImplement(Labelable::class);
});

it('implements Assignable interface', function () {
    expect(PullRequest::class)->toImplement(Assignable::class);
});

it('implements Closeable interface', function () {
    expect(PullRequest::class)->toImplement(Closeable::class);
});

it('implements Auditable interface', function () {
    expect(PullRequest::class)->toImplement(Auditable::class);
});

it('implements Reviewable interface', function () {
    expect(PullRequest::class)->toImplement(Reviewable::class);
});

it('implements Mergeable interface', function () {
    expect(PullRequest::class)->toImplement(Mergeable::class);
});

it('implements Checkable interface', function () {
    expect(PullRequest::class)->toImplement(Checkable::class);
});

it('implements Diffable interface', function () {
    expect(PullRequest::class)->toImplement(Diffable::class);
});

it('implements HasCommits interface', function () {
    expect(PullRequest::class)->toImplement(HasCommits::class);
});

it('can be type-hinted as Commentable', function () {
    $acceptsCommentable = function (Commentable $entity): string {
        return $entity::class;
    };

    $pr = createTestPr();
    expect($acceptsCommentable($pr))->toBe(PullRequest::class);
});

it('can be type-hinted as Reviewable', function () {
    $acceptsReviewable = function (Reviewable $entity): string {
        return $entity::class;
    };

    $pr = createTestPr();
    expect($acceptsReviewable($pr))->toBe(PullRequest::class);
});

it('can be type-hinted as Checkable', function () {
    $acceptsCheckable = function (Checkable $entity): string {
        return $entity::class;
    };

    $pr = createTestPr();
    expect($acceptsCheckable($pr))->toBe(PullRequest::class);
});

function createTestPr(): PullRequest
{
    $connector = createMockConnector([]);
    $prData = createTestPullRequestData();

    return new PullRequest($connector, 'owner', 'repo', $prData);
}
