# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is `conduit-ui/pr`, a PHP library for GitHub pull request automation. It provides a fluent API for approving, merging, commenting, and bulk-managing PRs programmatically. Part of the Conduit agent ecosystem.

## Commands

```bash
composer test          # Run Pest tests
composer format        # Fix code style with Laravel Pint
composer analyse       # Run PHPStan static analysis (level 8)
```

Run a single test:
```bash
./vendor/bin/pest --filter="test name"
./vendor/bin/pest tests/Unit/PullRequestTest.php
```

## Architecture

### Core Classes

- **`PullRequests`** - Static facade and factory. Entry point for all operations via `PullRequests::for()`, `::find()`, `::create()`, `::query()`. Requires connector setup via `PullRequests::setConnector()`.

- **`PullRequest`** - Wrapper around PR data with action methods (`approve()`, `merge()`, `close()`, `comment()`, `addLabels()`, etc.). Proxies property access to underlying DTO via `__get()`.

- **`QueryBuilder`** - Fluent query interface for listing PRs with filters (`state()`, `author()`, `label()`, `orderBy()`, `take()`).

### Data Transfer Objects (`src/DataTransferObjects/`)

All DTOs are immutable with `fromArray()` factory methods and `toArray()` serialization:
- `PullRequest` - Full PR data with nested `User`, `Label`, `Head`, `Base`
- `Head`/`Base` - Branch info with `Repository` reference
- `User`, `Label`, `Review`, `Comment`, `CheckRun`, `Repository`

### External Dependency

Uses `conduit-ui/connector` (`ConduitUi\GitHubConnector\Connector`) for GitHub API transport. The connector must be set before using static methods:

```php
use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\PullRequests;

PullRequests::setConnector(new Connector(getenv('GITHUB_TOKEN')));
```

## Code Style

- Laravel Pint with `laravel` preset
- `declare(strict_types=1)` required in all files
- Namespace: `ConduitUI\Pr`
