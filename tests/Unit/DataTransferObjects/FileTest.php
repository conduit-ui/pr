<?php

declare(strict_types=1);

use ConduitUI\Pr\DataTransferObjects\File;

it('can create file from array', function () {
    $data = [
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
    ];

    $file = File::fromArray($data);

    expect($file->sha)->toBe('abc123')
        ->and($file->filename)->toBe('src/Example.php')
        ->and($file->status)->toBe('modified')
        ->and($file->additions)->toBe(10)
        ->and($file->deletions)->toBe(5)
        ->and($file->changes)->toBe(15)
        ->and($file->blobUrl)->toBe('https://github.com/owner/repo/blob/abc123/src/Example.php')
        ->and($file->rawUrl)->toBe('https://github.com/owner/repo/raw/abc123/src/Example.php')
        ->and($file->contentsUrl)->toBe('https://api.github.com/repos/owner/repo/contents/src/Example.php')
        ->and($file->patch)->toBe('@@ -1,5 +1,10 @@')
        ->and($file->previousFilename)->toBeNull();
});

it('can create file with optional fields', function () {
    $data = [
        'sha' => 'def456',
        'filename' => 'src/NewName.php',
        'status' => 'renamed',
        'additions' => 0,
        'deletions' => 0,
        'changes' => 0,
        'blob_url' => 'https://github.com/owner/repo/blob/def456/src/NewName.php',
        'raw_url' => 'https://github.com/owner/repo/raw/def456/src/NewName.php',
        'contents_url' => 'https://api.github.com/repos/owner/repo/contents/src/NewName.php',
        'previous_filename' => 'src/OldName.php',
    ];

    $file = File::fromArray($data);

    expect($file->previousFilename)->toBe('src/OldName.php')
        ->and($file->patch)->toBeNull();
});

it('can check if file is added', function () {
    $file = File::fromArray([
        'sha' => 'abc123',
        'filename' => 'new.php',
        'status' => 'added',
        'additions' => 10,
        'deletions' => 0,
        'changes' => 10,
        'blob_url' => 'https://example.com',
        'raw_url' => 'https://example.com',
        'contents_url' => 'https://example.com',
    ]);

    expect($file->isAdded())->toBeTrue()
        ->and($file->isRemoved())->toBeFalse()
        ->and($file->isModified())->toBeFalse()
        ->and($file->isRenamed())->toBeFalse();
});

it('can check if file is removed', function () {
    $file = File::fromArray([
        'sha' => 'abc123',
        'filename' => 'deleted.php',
        'status' => 'removed',
        'additions' => 0,
        'deletions' => 10,
        'changes' => 10,
        'blob_url' => 'https://example.com',
        'raw_url' => 'https://example.com',
        'contents_url' => 'https://example.com',
    ]);

    expect($file->isRemoved())->toBeTrue()
        ->and($file->isAdded())->toBeFalse()
        ->and($file->isModified())->toBeFalse()
        ->and($file->isRenamed())->toBeFalse();
});

it('can check if file is modified', function () {
    $file = File::fromArray([
        'sha' => 'abc123',
        'filename' => 'changed.php',
        'status' => 'modified',
        'additions' => 5,
        'deletions' => 3,
        'changes' => 8,
        'blob_url' => 'https://example.com',
        'raw_url' => 'https://example.com',
        'contents_url' => 'https://example.com',
    ]);

    expect($file->isModified())->toBeTrue()
        ->and($file->isAdded())->toBeFalse()
        ->and($file->isRemoved())->toBeFalse()
        ->and($file->isRenamed())->toBeFalse();
});

it('can check if file is renamed', function () {
    $file = File::fromArray([
        'sha' => 'abc123',
        'filename' => 'new.php',
        'status' => 'renamed',
        'additions' => 0,
        'deletions' => 0,
        'changes' => 0,
        'blob_url' => 'https://example.com',
        'raw_url' => 'https://example.com',
        'contents_url' => 'https://example.com',
        'previous_filename' => 'old.php',
    ]);

    expect($file->isRenamed())->toBeTrue()
        ->and($file->isAdded())->toBeFalse()
        ->and($file->isRemoved())->toBeFalse()
        ->and($file->isModified())->toBeFalse();
});

it('can convert file to array', function () {
    $data = [
        'sha' => 'abc123',
        'filename' => 'test.php',
        'status' => 'modified',
        'additions' => 10,
        'deletions' => 5,
        'changes' => 15,
        'blob_url' => 'https://example.com/blob',
        'raw_url' => 'https://example.com/raw',
        'contents_url' => 'https://example.com/contents',
        'patch' => '@@ patch @@',
        'previous_filename' => 'old.php',
    ];

    $file = File::fromArray($data);
    $result = $file->toArray();

    expect($result)->toBe($data);
});
