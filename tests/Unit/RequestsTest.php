<?php

declare(strict_types=1);

use ConduitUI\Pr\Requests\AddAssignees;
use ConduitUI\Pr\Requests\AddIssueLabels;
use ConduitUI\Pr\Requests\CreateIssueComment;
use ConduitUI\Pr\Requests\CreatePullRequest;
use ConduitUI\Pr\Requests\CreatePullRequestComment;
use ConduitUI\Pr\Requests\CreatePullRequestReview;
use ConduitUI\Pr\Requests\GetCommitCheckRuns;
use ConduitUI\Pr\Requests\GetIssueComments;
use ConduitUI\Pr\Requests\GetIssueTimeline;
use ConduitUI\Pr\Requests\GetPullRequest;
use ConduitUI\Pr\Requests\GetPullRequestComments;
use ConduitUI\Pr\Requests\GetPullRequestCommits;
use ConduitUI\Pr\Requests\GetPullRequestDiff;
use ConduitUI\Pr\Requests\GetPullRequestFiles;
use ConduitUI\Pr\Requests\GetPullRequestReviews;
use ConduitUI\Pr\Requests\ListPullRequests;
use ConduitUI\Pr\Requests\MergePullRequest;
use ConduitUI\Pr\Requests\RemoveAssignees;
use ConduitUI\Pr\Requests\RemoveIssueLabel;
use ConduitUI\Pr\Requests\RemoveReviewers;
use ConduitUI\Pr\Requests\RequestReviewers;
use ConduitUI\Pr\Requests\UpdatePullRequest;

it('AddAssignees has correct endpoint', function () {
    $request = new AddAssignees('owner', 'repo', 123, ['user1', 'user2']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/assignees');
});

it('RemoveAssignees has correct endpoint', function () {
    $request = new RemoveAssignees('owner', 'repo', 123, ['user1']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/assignees');
});

it('GetIssueTimeline has correct endpoint', function () {
    $request = new GetIssueTimeline('owner', 'repo', 123);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/timeline');
});

it('AddIssueLabels has correct endpoint', function () {
    $request = new AddIssueLabels('owner', 'repo', 123, ['bug', 'enhancement']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/labels');
});

it('CreateIssueComment has correct endpoint', function () {
    $request = new CreateIssueComment('owner', 'repo', 123, 'Test comment');

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/comments');
});

it('CreatePullRequest has correct endpoint', function () {
    $request = new CreatePullRequest('owner', 'repo', ['title' => 'Test', 'head' => 'feature', 'base' => 'main']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls');
});

it('CreatePullRequestComment has correct endpoint', function () {
    $request = new CreatePullRequestComment('owner', 'repo', 123, 'Test comment', 'file.php', 10);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/comments');
});

it('CreatePullRequestReview has correct endpoint', function () {
    $request = new CreatePullRequestReview('owner', 'repo', 123, 'APPROVE', 'LGTM');

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/reviews');
});

it('GetCommitCheckRuns has correct endpoint', function () {
    $request = new GetCommitCheckRuns('owner', 'repo', 'abc123');

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/commits/abc123/check-runs');
});

it('GetIssueComments has correct endpoint', function () {
    $request = new GetIssueComments('owner', 'repo', 123);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/comments?per_page=100&page=1');
});

it('GetPullRequest has correct endpoint', function () {
    $request = new GetPullRequest('owner', 'repo', 123);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123');
});

it('GetPullRequestComments has correct endpoint', function () {
    $request = new GetPullRequestComments('owner', 'repo', 123);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/comments');
});

it('GetPullRequestCommits has correct endpoint', function () {
    $request = new GetPullRequestCommits('owner', 'repo', 123);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/commits?per_page=100&page=1');
});

it('GetPullRequestDiff has correct endpoint', function () {
    $request = new GetPullRequestDiff('owner', 'repo', 123);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123');
});

it('GetPullRequestFiles has correct endpoint', function () {
    $request = new GetPullRequestFiles('owner', 'repo', 123);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/files');
});

it('GetPullRequestReviews has correct endpoint', function () {
    $request = new GetPullRequestReviews('owner', 'repo', 123);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/reviews');
});

it('ListPullRequests has correct endpoint', function () {
    $request = new ListPullRequests('owner', 'repo', ['state' => 'open']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls?state=open');
});

it('MergePullRequest has correct endpoint', function () {
    $request = new MergePullRequest('owner', 'repo', 123, ['merge_method' => 'squash']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/merge');
});

it('RemoveIssueLabel has correct endpoint', function () {
    $request = new RemoveIssueLabel('owner', 'repo', 123, 'bug');

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/issues/123/labels/bug');
});

it('RemoveReviewers has correct endpoint', function () {
    $request = new RemoveReviewers('owner', 'repo', 123, ['user1'], ['team1']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/requested_reviewers');
});

it('RequestReviewers has correct endpoint', function () {
    $request = new RequestReviewers('owner', 'repo', 123, ['user1'], ['team1']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/requested_reviewers');
});

it('UpdatePullRequest has correct endpoint', function () {
    $request = new UpdatePullRequest('owner', 'repo', 123, ['title' => 'Updated']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123');
});

it('CreatePullRequestReview includes comments when provided', function () {
    $comments = [
        ['path' => 'file.php', 'line' => 10, 'body' => 'Fix this'],
    ];
    $request = new CreatePullRequestReview('owner', 'repo', 123, 'REQUEST_CHANGES', 'Please fix', $comments);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/reviews');
});

it('RequestReviewers handles empty team reviewers', function () {
    $request = new RequestReviewers('owner', 'repo', 123, ['user1'], []);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/requested_reviewers');
});

it('RemoveReviewers handles empty team reviewers', function () {
    $request = new RemoveReviewers('owner', 'repo', 123, ['user1'], []);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/pulls/123/requested_reviewers');
});

// Body method tests
it('AddAssignees has correct body', function () {
    $request = new AddAssignees('owner', 'repo', 123, ['user1', 'user2']);

    expect($request->body()->all())->toBe(['assignees' => ['user1', 'user2']]);
});

it('RemoveAssignees has correct body', function () {
    $request = new RemoveAssignees('owner', 'repo', 123, ['user1']);

    expect($request->body()->all())->toBe(['assignees' => ['user1']]);
});

it('AddIssueLabels has correct body', function () {
    $request = new AddIssueLabels('owner', 'repo', 123, ['bug', 'enhancement']);

    expect($request->body()->all())->toBe(['labels' => ['bug', 'enhancement']]);
});

it('CreateIssueComment has correct body', function () {
    $request = new CreateIssueComment('owner', 'repo', 123, 'Test comment');

    expect($request->body()->all())->toBe(['body' => 'Test comment']);
});

it('CreatePullRequest has correct body', function () {
    $data = ['title' => 'Test', 'head' => 'feature', 'base' => 'main'];
    $request = new CreatePullRequest('owner', 'repo', $data);

    expect($request->body()->all())->toBe($data);
});

it('CreatePullRequestComment has correct body', function () {
    $request = new CreatePullRequestComment('owner', 'repo', 123, 'Test comment', 'file.php', 10);

    expect($request->body()->all())->toBe([
        'body' => 'Test comment',
        'path' => 'file.php',
        'line' => 10,
    ]);
});

it('MergePullRequest has correct body', function () {
    $request = new MergePullRequest('owner', 'repo', 123, ['merge_method' => 'squash']);

    expect($request->body()->all())->toBe(['merge_method' => 'squash']);
});

it('UpdatePullRequest has correct body', function () {
    $request = new UpdatePullRequest('owner', 'repo', 123, ['title' => 'Updated']);

    expect($request->body()->all())->toBe(['title' => 'Updated']);
});

it('RequestReviewers has correct body with reviewers', function () {
    $request = new RequestReviewers('owner', 'repo', 123, ['user1', 'user2'], []);

    expect($request->body()->all())->toBe(['reviewers' => ['user1', 'user2']]);
});

it('RequestReviewers has correct body with team reviewers', function () {
    $request = new RequestReviewers('owner', 'repo', 123, [], ['team1']);

    expect($request->body()->all())->toBe(['team_reviewers' => ['team1']]);
});

it('RequestReviewers has correct body with both', function () {
    $request = new RequestReviewers('owner', 'repo', 123, ['user1'], ['team1']);

    expect($request->body()->all())->toBe([
        'reviewers' => ['user1'],
        'team_reviewers' => ['team1'],
    ]);
});

it('RemoveReviewers has correct body with reviewers', function () {
    $request = new RemoveReviewers('owner', 'repo', 123, ['user1'], []);

    expect($request->body()->all())->toBe(['reviewers' => ['user1']]);
});

it('RemoveReviewers has correct body with team reviewers', function () {
    $request = new RemoveReviewers('owner', 'repo', 123, [], ['team1']);

    expect($request->body()->all())->toBe(['team_reviewers' => ['team1']]);
});

it('RemoveReviewers has correct body with both', function () {
    $request = new RemoveReviewers('owner', 'repo', 123, ['user1'], ['team1']);

    expect($request->body()->all())->toBe([
        'reviewers' => ['user1'],
        'team_reviewers' => ['team1'],
    ]);
});

// Query and Headers tests
it('GetIssueTimeline has correct query parameters', function () {
    $request = new GetIssueTimeline('owner', 'repo', 123, 50, 2);

    expect($request->query()->all())->toBe(['per_page' => 50, 'page' => 2]);
});

it('GetIssueTimeline has correct headers', function () {
    $request = new GetIssueTimeline('owner', 'repo', 123);

    expect($request->headers()->all())->toHaveKey('Accept')
        ->and($request->headers()->get('Accept'))->toBe('application/vnd.github.mockingbird-preview+json');
});

it('GetPullRequestDiff has correct headers', function () {
    $request = new GetPullRequestDiff('owner', 'repo', 123);

    expect($request->headers()->all())->toHaveKey('Accept')
        ->and($request->headers()->get('Accept'))->toBe('application/vnd.github.v3.diff');
});
