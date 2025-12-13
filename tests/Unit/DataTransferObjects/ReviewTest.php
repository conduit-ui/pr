<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\Review;

it('can create review from array', function () {
    $review = Review::fromArray([
        'id' => 1,
        'user' => [
            'id' => 1,
            'login' => 'reviewer',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/reviewer',
            'type' => 'User',
        ],
        'body' => 'LGTM!',
        'state' => 'APPROVED',
        'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-1',
        'submitted_at' => '2025-01-01T10:00:00Z',
    ]);

    expect($review->id)->toBe(1)
        ->and($review->body)->toBe('LGTM!')
        ->and($review->state)->toBe('APPROVED')
        ->and($review->user->login)->toBe('reviewer');
});

it('can create review with null body', function () {
    $review = Review::fromArray([
        'id' => 1,
        'user' => [
            'id' => 1,
            'login' => 'reviewer',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/reviewer',
            'type' => 'User',
        ],
        'state' => 'APPROVED',
        'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-1',
        'submitted_at' => '2025-01-01T10:00:00Z',
    ]);

    expect($review->body)->toBeNull();
});

it('can convert review to array', function () {
    $review = Review::fromArray([
        'id' => 1,
        'user' => [
            'id' => 1,
            'login' => 'reviewer',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/reviewer',
            'type' => 'User',
        ],
        'body' => 'Needs work',
        'state' => 'CHANGES_REQUESTED',
        'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-1',
        'submitted_at' => '2025-01-01T10:00:00Z',
    ]);

    $array = $review->toArray();

    expect($array)->toBeArray()
        ->and($array['state'])->toBe('CHANGES_REQUESTED');
});

it('can check if review is approved', function () {
    $review = Review::fromArray([
        'id' => 1,
        'user' => [
            'id' => 1,
            'login' => 'reviewer',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/reviewer',
            'type' => 'User',
        ],
        'body' => 'LGTM',
        'state' => 'APPROVED',
        'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-1',
        'submitted_at' => '2025-01-01T10:00:00Z',
    ]);

    expect($review->isApproved())->toBeTrue()
        ->and($review->isChangesRequested())->toBeFalse()
        ->and($review->isCommented())->toBeFalse();
});

it('can check if review has changes requested', function () {
    $review = Review::fromArray([
        'id' => 1,
        'user' => [
            'id' => 1,
            'login' => 'reviewer',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/reviewer',
            'type' => 'User',
        ],
        'body' => 'Please fix',
        'state' => 'CHANGES_REQUESTED',
        'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-1',
        'submitted_at' => '2025-01-01T10:00:00Z',
    ]);

    expect($review->isChangesRequested())->toBeTrue()
        ->and($review->isApproved())->toBeFalse()
        ->and($review->isCommented())->toBeFalse();
});

it('can check if review is commented', function () {
    $review = Review::fromArray([
        'id' => 1,
        'user' => [
            'id' => 1,
            'login' => 'reviewer',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'html_url' => 'https://github.com/reviewer',
            'type' => 'User',
        ],
        'body' => 'Just a comment',
        'state' => 'COMMENTED',
        'html_url' => 'https://github.com/owner/repo/pull/1#pullrequestreview-1',
        'submitted_at' => '2025-01-01T10:00:00Z',
    ]);

    expect($review->isCommented())->toBeTrue()
        ->and($review->isApproved())->toBeFalse()
        ->and($review->isChangesRequested())->toBeFalse();
});
