<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\Repository;

it('can create repository from array', function () {
    $repo = Repository::fromArray([
        'id' => 1,
        'name' => 'repo',
        'full_name' => 'owner/repo',
        'html_url' => 'https://github.com/owner/repo',
        'private' => false,
    ]);

    expect($repo->id)->toBe(1)
        ->and($repo->name)->toBe('repo')
        ->and($repo->fullName)->toBe('owner/repo')
        ->and($repo->htmlUrl)->toBe('https://github.com/owner/repo')
        ->and($repo->private)->toBeFalse();
});

it('can create private repository', function () {
    $repo = Repository::fromArray([
        'id' => 1,
        'name' => 'private-repo',
        'full_name' => 'owner/private-repo',
        'html_url' => 'https://github.com/owner/private-repo',
        'private' => true,
    ]);

    expect($repo->private)->toBeTrue();
});

it('can convert repository to array', function () {
    $repo = Repository::fromArray([
        'id' => 1,
        'name' => 'repo',
        'full_name' => 'owner/repo',
        'html_url' => 'https://github.com/owner/repo',
        'private' => false,
    ]);

    $array = $repo->toArray();

    expect($array)->toBeArray()
        ->and($array['full_name'])->toBe('owner/repo');
});
