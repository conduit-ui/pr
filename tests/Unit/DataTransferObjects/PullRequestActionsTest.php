<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\PullRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->mockClient = new MockClient([
        MockResponse::make(['status' => 'success'], 200),
    ]);

    $this->connector = new Connector('fake-token');
    $this->connector->withMockClient($this->mockClient);

    $this->prData = [
        'number' => 123,
        'title' => 'Test PR',
        'body' => 'Test description',
        'state' => 'open',
        'user' => [
            'id' => 1,
            'login' => 'testuser',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/testuser',
            'type' => 'User',
        ],
        'html_url' => 'https://github.com/owner/repo/pull/123',
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-01T00:00:00Z',
        'draft' => false,
        'head' => [
            'ref' => 'feature-branch',
            'sha' => 'abc123',
            'user' => [
                'id' => 1,
                'login' => 'testuser',
                'avatar_url' => 'https://example.com/avatar.jpg',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'repo' => [
                'id' => 1,
                'name' => 'repo',
                'full_name' => 'owner/repo',
                'html_url' => 'https://github.com/owner/repo',
                'private' => false,
            ],
        ],
        'base' => [
            'ref' => 'main',
            'sha' => 'def456',
            'user' => [
                'id' => 1,
                'login' => 'testuser',
                'avatar_url' => 'https://example.com/avatar.jpg',
                'html_url' => 'https://github.com/testuser',
                'type' => 'User',
            ],
            'repo' => [
                'id' => 1,
                'name' => 'repo',
                'full_name' => 'owner/repo',
                'html_url' => 'https://github.com/owner/repo',
                'private' => false,
            ],
        ],
    ];
});

it('can create PR DTO with connector', function () {
    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');

    expect($pr)->toBeInstanceOf(PullRequest::class)
        ->and($pr->number)->toBe(123)
        ->and($pr->title)->toBe('Test PR');
});

it('can merge a PR', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['merged' => true], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->merge();

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can merge PR with squash strategy', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['merged' => true], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->merge('squash', 'Squashed commit');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can close a PR', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['state' => 'closed'], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->close();

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can reopen a PR', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['state' => 'open'], 200)
    );

    $closedPrData = array_merge($this->prData, ['state' => 'closed']);
    $pr = PullRequest::fromArray($closedPrData, $this->connector, 'owner', 'repo');
    $result = $pr->reopen();

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can mark PR as draft', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['draft' => true], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->markDraft();

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can mark PR as ready', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['draft' => false], 200)
    );

    $draftPrData = array_merge($this->prData, ['draft' => true]);
    $pr = PullRequest::fromArray($draftPrData, $this->connector, 'owner', 'repo');
    $result = $pr->markReady();

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can request reviewers', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['requested_reviewers' => [['login' => 'user1']]], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->requestReviewers(['user1', 'user2']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can request single reviewer', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['requested_reviewers' => [['login' => 'user1']]], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->requestReviewer('user1');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can request team reviewers', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['requested_teams' => [['slug' => 'team1']]], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->requestTeamReview('team1');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can add labels', function () {
    $this->mockClient->addResponse(
        MockResponse::make([
            ['name' => 'bug'],
            ['name' => 'ready-for-review'],
        ], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->addLabels(['bug', 'ready-for-review']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can add single label', function () {
    $this->mockClient->addResponse(
        MockResponse::make([['name' => 'bug']], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->addLabel('bug');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can remove label', function () {
    $this->mockClient->addResponse(
        MockResponse::make([], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->removeLabel('bug');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can set labels', function () {
    $this->mockClient->addResponse(
        MockResponse::make([
            ['name' => 'enhancement'],
            ['name' => 'documentation'],
        ], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->setLabels(['enhancement', 'documentation']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can assign users', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['assignees' => [['login' => 'user1']]], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->assign(['user1']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can assign single user', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['assignees' => [['login' => 'user1']]], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->assignUser('user1');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can unassign users', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['assignees' => []], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->unassign(['user1']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can approve PR', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['state' => 'APPROVED'], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->approve('LGTM!');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can request changes on PR', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['state' => 'CHANGES_REQUESTED'], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->requestChanges('Please fix the tests');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can comment on PR', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['body' => 'Test comment'], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->comment('Test comment');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can chain multiple actions', function () {
    $this->mockClient->addResponses([
        MockResponse::make([['name' => 'ready']], 200),
        MockResponse::make(['requested_reviewers' => [['login' => 'senior-dev']]], 200),
        MockResponse::make(['body' => 'Ready for review'], 200),
    ]);

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');

    $result = $pr->addLabel('ready')
        ->requestReviewer('senior-dev')
        ->comment('Ready for review');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('works without connector for read-only operations', function () {
    $pr = PullRequest::fromArray($this->prData);

    expect($pr)->toBeInstanceOf(PullRequest::class)
        ->and($pr->number)->toBe(123)
        ->and($pr->isOpen())->toBeTrue()
        ->and($pr->isDraft())->toBeFalse();
});

it('throws exception when trying to perform actions without connector', function () {
    $pr = PullRequest::fromArray($this->prData);

    expect(fn () => $pr->merge())->toThrow(\RuntimeException::class);
});

it('can use squashMerge helper', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['merged' => true], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->squashMerge('Squashed commit message');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can use rebaseMerge helper', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['merged' => true], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->rebaseMerge();

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can merge PR with custom title', function () {
    $this->mockClient->addResponse(
        MockResponse::make(['merged' => true], 200)
    );

    $pr = PullRequest::fromArray($this->prData, $this->connector, 'owner', 'repo');
    $result = $pr->merge('merge', 'Custom message', 'Custom title');

    expect($result)->toBeInstanceOf(PullRequest::class);
});
