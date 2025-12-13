<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\CheckRun;
use ConduitUI\Pr\DataTransferObjects\Comment;
use ConduitUI\Pr\DataTransferObjects\Commit;
use ConduitUI\Pr\DataTransferObjects\File;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\PullRequest;
use Saloon\Http\Request;
use Saloon\Http\Response;

class MockResponse extends Response
{
    public function __construct(private array $data, private ?string $bodyContent = null)
    {
        // Skip parent constructor
    }

    public function json(string|int|null $key = null, mixed $default = null): mixed
    {
        if ($key !== null) {
            return $this->data[$key] ?? $default;
        }

        return $this->data;
    }

    public function body(): string
    {
        return $this->bodyContent ?? '';
    }
}

class TestConnector extends Connector
{
    private int $callIndex = 0;

    /**
     * @param  array<int, array<string, mixed>|string>  $responses
     */
    public function __construct(private array $responses = [])
    {
        parent::__construct('test-token');
    }

    public function send(Request $request, ...$args): Response
    {
        $response = $this->responses[$this->callIndex++] ?? [];

        // If response is a string, treat it as body content
        if (is_string($response)) {
            return new MockResponse([], $response);
        }

        return new MockResponse($response);
    }
}

function createMockConnector(array $responses = []): Connector
{
    return new TestConnector($responses);
}

function createTestPullRequestData(): PullRequestData
{
    return PullRequestData::fromArray([
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
    ]);
}

it('can get commits from pull request', function () {
    $mockCommits = [
        [
            'sha' => 'commit1',
            'commit' => [
                'message' => 'First commit',
                'author' => [
                    'name' => 'Author 1',
                    'email' => 'author1@example.com',
                    'date' => '2025-01-01T10:00:00Z',
                ],
                'committer' => [
                    'name' => 'Committer 1',
                    'email' => 'committer1@example.com',
                    'date' => '2025-01-01T10:05:00Z',
                ],
            ],
            'html_url' => 'https://github.com/owner/repo/commit/commit1',
        ],
        [
            'sha' => 'commit2',
            'commit' => [
                'message' => 'Second commit',
                'author' => [
                    'name' => 'Author 2',
                    'email' => 'author2@example.com',
                    'date' => '2025-01-01T11:00:00Z',
                ],
                'committer' => [
                    'name' => 'Committer 2',
                    'email' => 'committer2@example.com',
                    'date' => '2025-01-01T11:05:00Z',
                ],
            ],
            'html_url' => 'https://github.com/owner/repo/commit/commit2',
        ],
    ];

    $connector = createMockConnector([$mockCommits]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $commits = $pr->commits();

    expect($commits)->toBeArray()
        ->and($commits)->toHaveCount(2)
        ->and($commits[0])->toBeInstanceOf(Commit::class)
        ->and($commits[0]->sha)->toBe('commit1')
        ->and($commits[0]->message)->toBe('First commit')
        ->and($commits[1])->toBeInstanceOf(Commit::class)
        ->and($commits[1]->sha)->toBe('commit2')
        ->and($commits[1]->message)->toBe('Second commit');
});

it('returns empty array when no commits', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $commits = $pr->commits();

    expect($commits)->toBeArray()
        ->and($commits)->toBeEmpty();
});

it('can get issue comments from pull request', function () {
    $mockComments = [
        [
            'id' => 1,
            'user' => [
                'id' => 1,
                'login' => 'commenter1',
                'avatar_url' => 'https://example.com/avatar1.jpg',
                'html_url' => 'https://github.com/commenter1',
                'type' => 'User',
            ],
            'body' => 'First comment',
            'html_url' => 'https://github.com/owner/repo/issues/123#issuecomment-1',
            'created_at' => '2025-01-01T10:00:00Z',
            'updated_at' => '2025-01-01T10:00:00Z',
        ],
        [
            'id' => 2,
            'user' => [
                'id' => 2,
                'login' => 'commenter2',
                'avatar_url' => 'https://example.com/avatar2.jpg',
                'html_url' => 'https://github.com/commenter2',
                'type' => 'User',
            ],
            'body' => 'Second comment',
            'html_url' => 'https://github.com/owner/repo/issues/123#issuecomment-2',
            'created_at' => '2025-01-01T11:00:00Z',
            'updated_at' => '2025-01-01T11:00:00Z',
        ],
    ];

    $connector = createMockConnector([$mockComments]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $comments = $pr->issueComments();

    expect($comments)->toBeArray()
        ->and($comments)->toHaveCount(2)
        ->and($comments[0])->toBeInstanceOf(Comment::class)
        ->and($comments[0]->id)->toBe(1)
        ->and($comments[0]->body)->toBe('First comment')
        ->and($comments[1])->toBeInstanceOf(Comment::class)
        ->and($comments[1]->id)->toBe(2)
        ->and($comments[1]->body)->toBe('Second comment');
});

it('returns empty array when no issue comments', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $comments = $pr->issueComments();

    expect($comments)->toBeArray()
        ->and($comments)->toBeEmpty();
});

it('paginates commits across multiple pages', function () {
    // First page: 100 commits (full page triggers next request)
    $page1 = array_map(fn ($i) => [
        'sha' => "commit-page1-{$i}",
        'commit' => [
            'message' => "Commit {$i}",
            'author' => [
                'name' => "Author {$i}",
                'email' => "author{$i}@example.com",
                'date' => '2025-01-01T10:00:00Z',
            ],
            'committer' => [
                'name' => "Committer {$i}",
                'email' => "committer{$i}@example.com",
                'date' => '2025-01-01T10:05:00Z',
            ],
        ],
        'html_url' => "https://github.com/owner/repo/commit/commit-page1-{$i}",
    ], range(1, 100));

    // Second page: 50 commits (partial page, stops pagination)
    $page2 = array_map(fn ($i) => [
        'sha' => "commit-page2-{$i}",
        'commit' => [
            'message' => "Commit {$i}",
            'author' => [
                'name' => "Author {$i}",
                'email' => "author{$i}@example.com",
                'date' => '2025-01-01T10:00:00Z',
            ],
            'committer' => [
                'name' => "Committer {$i}",
                'email' => "committer{$i}@example.com",
                'date' => '2025-01-01T10:05:00Z',
            ],
        ],
        'html_url' => "https://github.com/owner/repo/commit/commit-page2-{$i}",
    ], range(1, 50));

    $connector = createMockConnector([$page1, $page2]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $commits = $pr->commits();

    expect($commits)->toBeArray()
        ->and($commits)->toHaveCount(150)
        ->and($commits[0])->toBeInstanceOf(Commit::class)
        ->and($commits[0]->sha)->toBe('commit-page1-1')
        ->and($commits[99])->toBeInstanceOf(Commit::class)
        ->and($commits[99]->sha)->toBe('commit-page1-100')
        ->and($commits[100])->toBeInstanceOf(Commit::class)
        ->and($commits[100]->sha)->toBe('commit-page2-1')
        ->and($commits[149])->toBeInstanceOf(Commit::class)
        ->and($commits[149]->sha)->toBe('commit-page2-50');
});

it('paginates issue comments across multiple pages', function () {
    // First page: 100 comments
    $page1 = array_map(fn ($i) => [
        'id' => $i,
        'user' => [
            'id' => 1,
            'login' => 'user',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/user',
            'type' => 'User',
        ],
        'body' => "Comment {$i}",
        'html_url' => "https://github.com/owner/repo/issues/123#issuecomment-{$i}",
        'created_at' => '2025-01-01T10:00:00Z',
        'updated_at' => '2025-01-01T10:00:00Z',
    ], range(1, 100));

    // Second page: 25 comments
    $page2 = array_map(fn ($i) => [
        'id' => 100 + $i,
        'user' => [
            'id' => 1,
            'login' => 'user',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/user',
            'type' => 'User',
        ],
        'body' => 'Comment '.(100 + $i),
        'html_url' => 'https://github.com/owner/repo/issues/123#issuecomment-'.(100 + $i),
        'created_at' => '2025-01-01T10:00:00Z',
        'updated_at' => '2025-01-01T10:00:00Z',
    ], range(1, 25));

    $connector = createMockConnector([$page1, $page2]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $comments = $pr->issueComments();

    expect($comments)->toBeArray()
        ->and($comments)->toHaveCount(125)
        ->and($comments[0])->toBeInstanceOf(Comment::class)
        ->and($comments[0]->id)->toBe(1)
        ->and($comments[99]->id)->toBe(100)
        ->and($comments[100]->id)->toBe(101)
        ->and($comments[124]->id)->toBe(125);
});

it('can get diff from pull request', function () {
    $mockDiff = <<<'DIFF'
diff --git a/file.php b/file.php
index abc123..def456 100644
--- a/file.php
+++ b/file.php
@@ -1,3 +1,4 @@
 <?php
+echo "Hello World";

DIFF;

    $connector = createMockConnector([$mockDiff]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $diff = $pr->diff();

    expect($diff)->toBeString()
        ->and($diff)->toContain('diff --git')
        ->and($diff)->toContain('echo "Hello World"');
    $mockFiles = [
        [
            'sha' => 'abc123',
            'filename' => 'src/Example.php',
            'status' => 'modified',
            'additions' => 10,
            'deletions' => 5,
            'changes' => 15,
            'blob_url' => 'https://github.com/owner/repo/blob/abc123/src/Example.php',
            'raw_url' => 'https://github.com/owner/repo/raw/abc123/src/Example.php',
            'contents_url' => 'https://api.github.com/repos/owner/repo/contents/src/Example.php',
            'patch' => '@@ -1,5 +1,10 @@',
        ],
        [
            'sha' => 'def456',
            'filename' => 'tests/ExampleTest.php',
            'status' => 'added',
            'additions' => 20,
            'deletions' => 0,
            'changes' => 20,
            'blob_url' => 'https://github.com/owner/repo/blob/def456/tests/ExampleTest.php',
            'raw_url' => 'https://github.com/owner/repo/raw/def456/tests/ExampleTest.php',
            'contents_url' => 'https://api.github.com/repos/owner/repo/contents/tests/ExampleTest.php',
            'patch' => '@@ -0,0 +1,20 @@',
        ],
    ];

    $connector = createMockConnector([$mockFiles]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $files = $pr->files();

    expect($files)->toBeArray()
        ->and($files)->toHaveCount(2)
        ->and($files[0])->toBeInstanceOf(File::class)
        ->and($files[0]->filename)->toBe('src/Example.php')
        ->and($files[0]->status)->toBe('modified')
        ->and($files[0]->additions)->toBe(10)
        ->and($files[1])->toBeInstanceOf(File::class)
        ->and($files[1]->filename)->toBe('tests/ExampleTest.php')
        ->and($files[1]->status)->toBe('added');
});

it('returns empty array when no files', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $files = $pr->files();

    expect($files)->toBeArray()
        ->and($files)->toBeEmpty();
});

it('can get checks from pull request', function () {
    $mockChecks = [
        'check_runs' => [
            [
                'id' => 1,
                'name' => 'PHPStan',
                'status' => 'completed',
                'conclusion' => 'success',
                'html_url' => 'https://github.com/owner/repo/runs/1',
                'started_at' => '2025-01-01T10:00:00Z',
                'completed_at' => '2025-01-01T10:05:00Z',
            ],
            [
                'id' => 2,
                'name' => 'Pest',
                'status' => 'completed',
                'conclusion' => 'failure',
                'html_url' => 'https://github.com/owner/repo/runs/2',
                'started_at' => '2025-01-01T10:01:00Z',
                'completed_at' => '2025-01-01T10:06:00Z',
            ],
        ],
    ];

    $connector = createMockConnector([$mockChecks]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $checks = $pr->checks();

    expect($checks)->toBeArray()
        ->and($checks)->toHaveCount(2)
        ->and($checks[0])->toBeInstanceOf(CheckRun::class)
        ->and($checks[0]->name)->toBe('PHPStan')
        ->and($checks[0]->conclusion)->toBe('success')
        ->and($checks[1])->toBeInstanceOf(CheckRun::class)
        ->and($checks[1]->name)->toBe('Pest')
        ->and($checks[1]->conclusion)->toBe('failure');
});

it('returns empty array when no checks', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $checks = $pr->checks();

    expect($checks)->toBeArray()
        ->and($checks)->toBeEmpty();
});

it('can assign users to pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->assign(['user1', 'user2']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can unassign users from pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->unassign(['user1']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can get timeline from pull request', function () {
    $mockTimeline = [
        [
            'event' => 'labeled',
            'label' => ['name' => 'bug'],
            'created_at' => '2025-01-01T10:00:00Z',
        ],
        [
            'event' => 'commented',
            'body' => 'This is a comment',
            'created_at' => '2025-01-01T11:00:00Z',
        ],
    ];

    $connector = createMockConnector([$mockTimeline]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $timeline = $pr->timeline();

    expect($timeline)->toBeArray()
        ->and($timeline)->toHaveCount(2)
        ->and($timeline[0]['event'])->toBe('labeled')
        ->and($timeline[1]['event'])->toBe('commented');
});

it('returns empty array when no timeline events', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $timeline = $pr->timeline();

    expect($timeline)->toBeArray()
        ->and($timeline)->toBeEmpty();
});

it('can add labels to pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->addLabels(['bug', 'enhancement']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can remove label from pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->removeLabel('bug');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can add reviewers to pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->addReviewers(['reviewer1'], ['team1']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can remove reviewers from pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->removeReviewers(['reviewer1'], ['team1']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can close pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->close();

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can reopen pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->reopen();

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can update pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->update(['title' => 'New title']);

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can merge pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->merge('squash', 'Commit title', 'Commit message');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can add comment to pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->comment('This is a comment');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can add inline comment to pull request', function () {
    $connector = createMockConnector([[]]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $result = $pr->comment('Inline comment', 10, 'src/file.php');

    expect($result)->toBeInstanceOf(PullRequest::class);
});

it('can get reviews from pull request', function () {
    $mockReviews = [
        [
            'id' => 1,
            'user' => [
                'id' => 1,
                'login' => 'reviewer1',
                'avatar_url' => 'https://example.com/avatar.jpg',
                'html_url' => 'https://github.com/reviewer1',
                'type' => 'User',
            ],
            'body' => 'Looks good!',
            'state' => 'APPROVED',
            'html_url' => 'https://github.com/owner/repo/pull/123#pullrequestreview-1',
            'submitted_at' => '2025-01-01T10:00:00Z',
        ],
    ];

    $connector = createMockConnector([$mockReviews]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $reviews = $pr->reviews();

    expect($reviews)->toBeArray()
        ->and($reviews)->toHaveCount(1)
        ->and($reviews[0]->state)->toBe('APPROVED');
});

it('can convert pull request to array', function () {
    $connector = createMockConnector([]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    $array = $pr->toArray();

    expect($array)->toBeArray()
        ->and($array['number'])->toBe(123)
        ->and($array['title'])->toBe('Test PR');
});

it('can access pull request data via magic getter', function () {
    $connector = createMockConnector([]);
    $prData = createTestPullRequestData();
    $pr = new PullRequest($connector, 'owner', 'repo', $prData);

    expect($pr->number)->toBe(123)
        ->and($pr->title)->toBe('Test PR')
        ->and($pr->state)->toBe('open');
});
