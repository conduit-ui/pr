# conduit-ui/prs

Expressive GitHub Pull Request management for PHP, inspired by Taylor Otwell's API design philosophy.

## Installation

```bash
composer require conduit-ui/prs
```

## Usage

### Static API (Recommended)

The static API provides a clean, expressive interface for working with pull requests:

```php
use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Prs\PullRequests;

// Configure once
PullRequests::setConnector(new Connector('your-github-token'));

// Find a specific PR
$pr = PullRequests::find('owner/repo', 123);

// Create a new PR
$pr = PullRequests::create('owner/repo', [
    'title' => 'Add new feature',
    'head' => 'feature-branch',
    'base' => 'main',
    'body' => 'Description here',
]);

// Work with the PR
$pr->approve('LGTM!')
   ->merge('squash');

// Query with fluent interface
$prs = PullRequests::for('owner/repo')
    ->open()
    ->author('username')
    ->orderBy('updated', 'desc')
    ->take(10)
    ->get();

// Advanced query builder
$prs = PullRequests::query()
    ->repository('owner/repo')
    ->state('closed')
    ->label('bug')
    ->get();
```

### Instance API

For more traditional object-oriented usage:

```php
use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Prs\PullRequests;

$connector = new Connector('your-github-token');
$prs = new PullRequests($connector, 'owner', 'repo');

// Get a specific pull request
$pr = $prs->get(123);

// List all open pull requests
$openPrs = $prs->open();

// Create a pull request
$pr = $prs->createPullRequest([
    'title' => 'Add new feature',
    'body' => 'This PR adds...',
    'head' => 'feature-branch',
    'base' => 'main',
]);

// Merge or close
$prs->merge(123, 'Custom merge message', 'squash');
$prs->close(123);
```

## Working with Pull Requests

Once you have a `PullRequest` object, you can perform various actions:

```php
// Reviews
$pr->approve('Looks good!');
$pr->requestChanges('Please fix the following...');
$pr->comment('Great work on this implementation!');

// Inline comments
$pr->comment('Consider refactoring this', line: 42, path: 'src/File.php');

// Merging
$pr->merge('squash');  // or 'merge', 'rebase'
$pr->merge('squash', 'Custom title', 'Custom message');

// State management
$pr->close();
$pr->reopen();
$pr->update(['title' => 'New title']);

// Labels and reviewers
$pr->addLabels(['bug', 'high-priority']);
$pr->removeLabel('needs-review');
$pr->addReviewers(['alice', 'bob'], teamReviewers: ['team-leads']);

// Fetch related data
$reviews = $pr->reviews();
$comments = $pr->comments();
$files = $pr->files();
$checks = $pr->checks();

// Access PR data
echo $pr->data->title;
echo $pr->data->state;
echo $pr->data->user->login;
echo $pr->data->createdAt->format('Y-m-d');

// Or use magic getters
echo $pr->title;
echo $pr->number;
```

## Query Builder

The query builder provides a fluent interface for filtering pull requests:

```php
PullRequests::query()
    ->repository('owner/repo')  // Required
    ->state('open')             // 'open', 'closed', or 'all'
    ->author('username')        // Filter by author
    ->label('bug')              // Filter by label
    ->orderBy('updated', 'desc') // Sort by created, updated, popularity, or long-running
    ->take(20)                  // Limit results
    ->page(2)                   // Pagination
    ->get();                    // Execute query

// Convenience methods
PullRequests::for('owner/repo')->open()->get();
PullRequests::for('owner/repo')->closed()->get();
PullRequests::for('owner/repo')->all()->get();
```

## Design Philosophy

This package follows Taylor Otwell's package design principles:

- **Expressive API**: Methods read like English (`$pr->approve()`, `$pr->requestChanges()`)
- **Fluent interfaces**: Enable chaining for elegant, readable code
- **Clean DTOs**: Strongly-typed data transfer objects for all API responses
- **Minimal dependencies**: Only depends on conduit-ui/connector
- **Convention over configuration**: Sensible defaults, minimal setup required
- **Both static and instance APIs**: Use whichever style fits your needs

## Available DTOs

- `PullRequest` - Complete pull request data with status helpers
- `Review` - Pull request review data
- `Comment` - Pull request comment data
- `CheckRun` - CI/CD check run data
- `User` - GitHub user data
- `Label` - Issue/PR label data
- `Repository` - Repository data
- `Head` / `Base` - Branch reference data

## Testing

```bash
composer test
```

## Code Style

```bash
vendor/bin/pint
```

## License

MIT
