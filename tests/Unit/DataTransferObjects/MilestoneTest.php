<?php

declare(strict_types=1);

use Carbon\Carbon;
use ConduitUI\Pr\DataTransferObjects\Milestone;

beforeEach(function () {
    Carbon::setTestNow('2025-01-15 12:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

it('can create milestone from array', function () {
    $milestone = Milestone::fromArray([
        'number' => 1,
        'title' => 'v1.0',
        'description' => 'First release',
        'state' => 'open',
        'open_issues' => 5,
        'closed_issues' => 10,
        'due_on' => '2025-12-31T23:59:59Z',
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    expect($milestone->number)->toBe(1)
        ->and($milestone->title)->toBe('v1.0')
        ->and($milestone->description)->toBe('First release')
        ->and($milestone->state)->toBe('open')
        ->and($milestone->openIssues)->toBe(5)
        ->and($milestone->closedIssues)->toBe(10)
        ->and($milestone->dueOn)->toBeInstanceOf(Carbon::class)
        ->and($milestone->createdAt)->toBeInstanceOf(Carbon::class)
        ->and($milestone->updatedAt)->toBeInstanceOf(Carbon::class)
        ->and($milestone->closedAt)->toBeNull()
        ->and($milestone->htmlUrl)->toBe('https://github.com/test/repo/milestone/1');
});

it('can create milestone from array with null description and due date', function () {
    $milestone = Milestone::fromArray([
        'number' => 2,
        'title' => 'v2.0',
        'description' => null,
        'state' => 'closed',
        'open_issues' => 0,
        'closed_issues' => 15,
        'due_on' => null,
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => '2025-01-15T00:00:00Z',
        'html_url' => 'https://github.com/test/repo/milestone/2',
    ]);

    expect($milestone->description)->toBeNull()
        ->and($milestone->dueOn)->toBeNull()
        ->and($milestone->closedAt)->toBeInstanceOf(Carbon::class);
});

it('can convert milestone to array', function () {
    $milestone = Milestone::fromArray([
        'number' => 1,
        'title' => 'v1.0',
        'description' => 'First release',
        'state' => 'open',
        'open_issues' => 5,
        'closed_issues' => 10,
        'due_on' => '2025-12-31T23:59:59Z',
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    $array = $milestone->toArray();

    expect($array)->toBeArray()
        ->and($array['number'])->toBe(1)
        ->and($array['title'])->toBe('v1.0')
        ->and($array['description'])->toBe('First release')
        ->and($array['state'])->toBe('open');
});

it('correctly identifies open milestone', function () {
    $milestone = Milestone::fromArray([
        'number' => 1,
        'title' => 'v1.0',
        'description' => null,
        'state' => 'open',
        'open_issues' => 5,
        'closed_issues' => 10,
        'due_on' => null,
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    expect($milestone->isOpen())->toBeTrue()
        ->and($milestone->isClosed())->toBeFalse();
});

it('correctly identifies closed milestone', function () {
    $milestone = Milestone::fromArray([
        'number' => 1,
        'title' => 'v1.0',
        'description' => null,
        'state' => 'closed',
        'open_issues' => 0,
        'closed_issues' => 15,
        'due_on' => null,
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => '2025-01-15T00:00:00Z',
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    expect($milestone->isClosed())->toBeTrue()
        ->and($milestone->isOpen())->toBeFalse();
});

it('correctly identifies overdue milestone', function () {
    $milestone = Milestone::fromArray([
        'number' => 1,
        'title' => 'v1.0',
        'description' => null,
        'state' => 'open',
        'open_issues' => 5,
        'closed_issues' => 10,
        'due_on' => '2025-01-01T00:00:00Z', // Past date
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    expect($milestone->isOverdue())->toBeTrue();
});

it('identifies non-overdue milestone', function () {
    $milestone = Milestone::fromArray([
        'number' => 1,
        'title' => 'v1.0',
        'description' => null,
        'state' => 'open',
        'open_issues' => 5,
        'closed_issues' => 10,
        'due_on' => '2025-12-31T23:59:59Z', // Future date
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    expect($milestone->isOverdue())->toBeFalse();
});

it('identifies closed milestone as not overdue', function () {
    $milestone = Milestone::fromArray([
        'number' => 1,
        'title' => 'v1.0',
        'description' => null,
        'state' => 'closed',
        'open_issues' => 0,
        'closed_issues' => 15,
        'due_on' => '2025-01-01T00:00:00Z', // Past date but closed
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => '2025-01-14T00:00:00Z',
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    expect($milestone->isOverdue())->toBeFalse();
});

it('calculates progress correctly', function () {
    $milestone = Milestone::fromArray([
        'number' => 1,
        'title' => 'v1.0',
        'description' => null,
        'state' => 'open',
        'open_issues' => 5,
        'closed_issues' => 10,
        'due_on' => null,
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    expect($milestone->progress())->toBe(66.67);
});

it('returns zero progress when no issues exist', function () {
    $milestone = Milestone::fromArray([
        'number' => 1,
        'title' => 'v1.0',
        'description' => null,
        'state' => 'open',
        'open_issues' => 0,
        'closed_issues' => 0,
        'due_on' => null,
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    expect($milestone->progress())->toBe(0.0);
});

it('returns 100 percent progress when all issues closed', function () {
    $milestone = Milestone::fromArray([
        'number' => 1,
        'title' => 'v1.0',
        'description' => null,
        'state' => 'open',
        'open_issues' => 0,
        'closed_issues' => 15,
        'due_on' => null,
        'created_at' => '2025-01-01T00:00:00Z',
        'updated_at' => '2025-01-15T00:00:00Z',
        'closed_at' => null,
        'html_url' => 'https://github.com/test/repo/milestone/1',
    ]);

    expect($milestone->progress())->toBe(100.0);
});
