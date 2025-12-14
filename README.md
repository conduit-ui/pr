# PR Automation for Teams That Ship Fast

Stop context-switching between PRs. Start automating approvals, merges, and bulk operations.

Approve, merge, request changes, and manage pull requests at scale with expressive PHP code. Built for teams shipping multiple releases per day.

[![Sentinel Certified](https://img.shields.io/github/actions/workflow/status/conduit-ui/pr/gate.yml?label=Sentinel%20Certified&style=flat-square)](https://github.com/conduit-ui/pr/actions/workflows/gate.yml)
[![Latest Version](https://img.shields.io/packagist/v/conduit-ui/pr.svg?style=flat-square)](https://packagist.org/packages/conduit-ui/pr)
[![MIT License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/conduit-ui/pr.svg?style=flat-square)](https://packagist.org/packages/conduit-ui/pr)

## Installation

```bash
composer require conduit-ui/pr
```

## Why This Exists

Your team merges 30+ PRs per day. You're manually checking CI status, approving dependency updates, and enforcing review policies. This package automates the repetitive parts so your team can focus on code review that matters.

## Quick Start

```php
use ConduitUI\Pr\PullRequest;

// Approve and merge a PR
PullRequest::find('owner/repo', 123)
    ->approve('LGTM! Shipping it.')
    ->merge();

// Auto-merge all passing Dependabot PRs
PullRequest::query('owner/repo')
    ->author('dependabot[bot]')
    ->open()
    ->get()
    ->filter(fn($pr) => $pr->checksPass())
    ->each(fn($pr) => $pr->merge());
```

## Core Features

### Review Actions

**Approve PRs**
```php
PullRequest::find('owner/repo', 456)
    ->approve('Great work! Tests look good.');
```

**Request Changes**
```php
PullRequest::find('owner/repo', 789)
    ->requestChanges('Please add tests for the new feature.');
```

**Add Review Comments**
```php
PullRequest::find('owner/repo', 101)
    ->comment('Consider extracting this logic into a separate class.');
```

**Inline Code Comments**
```php
PullRequest::find('owner/repo', 202)
    ->addInlineComment(
        path: 'src/Service.php',
        line: 42,
        comment: 'This could cause a race condition'
    );
```

### Merge Operations

**Simple Merge**
```php
$pr = PullRequest::find('owner/repo', 123);
$pr->merge(); // Merge commit (default)
```

**Merge Strategies**
```php
$pr->merge(strategy: 'squash'); // Squash and merge
$pr->merge(strategy: 'rebase'); // Rebase and merge
```

**Conditional Merge**
```php
$pr = PullRequest::find('owner/repo', 123);

if ($pr->checksPass() && $pr->approved()) {
    $pr->merge();
}
```

### State Management

**Close & Reopen**
```php
// Close without merging
$pr->close();

// Reopen a closed PR
$pr->reopen();
```

### Advanced Queries

**Filter by State**
```php
PullRequest::query('owner/repo')
    ->state('open')
    ->get();
```

**Filter by Author**
```php
PullRequest::query('owner/repo')
    ->author('username')
    ->get();
```

**Filter by Labels**
```php
PullRequest::query('owner/repo')
    ->labels(['ready-to-merge', 'hotfix'])
    ->get();
```

**Sort Results**
```php
PullRequest::query('owner/repo')
    ->sort('created', 'desc')
    ->get();
```

**Convenience Methods**
```php
// All open PRs
PullRequest::query('owner/repo')->open()->get();

// All closed PRs
PullRequest::query('owner/repo')->closed()->get();

// All merged PRs
PullRequest::query('owner/repo')->merged()->get();
```

## Real-World Automation

### Auto-Merge Dependabot

```php
// Run this on a schedule (every 15 minutes)
PullRequest::query('owner/repo')
    ->author('dependabot[bot]')
    ->open()
    ->get()
    ->filter(fn($pr) => $pr->checksPass())
    ->filter(fn($pr) => $pr->title->contains(['patch', 'minor']))
    ->each(function($pr) {
        $pr->approve('Auto-approving passing dependency update');
        $pr->merge(strategy: 'squash');
    });
```

### Enforce Review Policy

```php
// Block merge if not approved by 2+ reviewers
$pr = PullRequest::find('owner/repo', 123);

if ($pr->approvals()->count() < 2) {
    $pr->comment('⚠️ This PR requires 2 approvals before merging.');
    exit(1);
}
```

### Bulk Label Management

```php
// Add "shipped" label to all merged PRs from this week
PullRequest::query('owner/repo')
    ->merged()
    ->since(now()->startOfWeek())
    ->get()
    ->each(fn($pr) => $pr->addLabels(['shipped']));
```

### Hotfix Fast-Track

```php
// Auto-merge hotfixes that pass CI
PullRequest::query('owner/repo')
    ->label('hotfix')
    ->open()
    ->get()
    ->filter(fn($pr) => $pr->checksPass())
    ->each(function($pr) {
        $pr->approve('Hotfix approved via automation');
        $pr->merge(strategy: 'squash');
    });
```

## Usage Patterns

### Static API (Recommended)
```php
use ConduitUI\Pr\PullRequest;

$pr = PullRequest::find('owner/repo', 123);
$prs = PullRequest::query('owner/repo')->open()->get();
```

### Instance API
```php
use ConduitUI\Pr\PullRequestManager;

$manager = new PullRequestManager('owner/repo');
$pr = $manager->find(123);
$prs = $manager->query()->open()->get();
```

## Data Objects

All responses return strongly-typed DTOs:

```php
$pr->id;              // int
$pr->number;          // int
$pr->title;           // string
$pr->state;           // 'open' | 'closed' | 'merged'
$pr->author;          // User object
$pr->reviewers;       // Collection of User objects
$pr->labels;          // Collection of Label objects
$pr->headBranch;      // string
$pr->baseBranch;      // string
$pr->mergeable;       // bool
$pr->createdAt;       // Carbon instance
$pr->updatedAt;       // Carbon instance
$pr->mergedAt;        // ?Carbon instance
$pr->checksPass();    // bool - All CI checks passing
$pr->approved();      // bool - Has approvals
$pr->approvals();     // Collection of Review objects
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="pr-config"
```

Set your GitHub token in `.env`:

```env
GITHUB_TOKEN=your-github-token
```

## Requirements

- PHP 8.2+
- GitHub personal access token with `repo` scope

## Testing

```bash
composer test
```

## Code Quality

```bash
composer format  # Fix code style
composer analyse # Run static analysis
```

## Related Packages

- [conduit-ui/issue](https://github.com/conduit-ui/issue) - Issue triage automation
- [conduit-ui/repo](https://github.com/conduit-ui/repo) - Repository governance
- [conduit-ui/connector](https://github.com/conduit-ui/connector) - GitHub API transport layer

## Enterprise Support

Automating PR workflows across your organization? Contact [jordan@partridge.rocks](mailto:jordan@partridge.rocks) for custom solutions including compliance checks, advanced approval rules, and audit logging.

## License

MIT License. See [LICENSE](LICENSE.md) for details.
