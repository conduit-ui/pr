<?php

declare(strict_types=1);

use ConduitUI\Pr\Contracts\CheckRunQueryInterface;
use ConduitUI\Pr\Contracts\CommentManagerInterface;
use ConduitUI\Pr\Contracts\FileQueryInterface;
use ConduitUI\Pr\Contracts\MergeManagerInterface;
use ConduitUI\Pr\Contracts\PullRequestBuilderInterface;
use ConduitUI\Pr\Contracts\PullRequestManagerInterface;
use ConduitUI\Pr\Contracts\PullRequestQueryInterface;
use ConduitUI\Pr\Contracts\ReviewBuilderInterface;
use ConduitUI\Pr\Contracts\ReviewQueryInterface;

describe('PullRequestManagerInterface', function () {
    it('defines all required manager methods', function () {
        $reflection = new ReflectionClass(PullRequestManagerInterface::class);

        expect($reflection->hasMethod('find'))->toBeTrue()
            ->and($reflection->hasMethod('get'))->toBeTrue()
            ->and($reflection->hasMethod('query'))->toBeTrue()
            ->and($reflection->hasMethod('create'))->toBeTrue();
    });

    it('returns PullRequestQueryInterface from query method', function () {
        $reflection = new ReflectionClass(PullRequestManagerInterface::class);
        $method = $reflection->getMethod('query');

        expect($method->getReturnType()?->getName())->toBe(PullRequestQueryInterface::class);
    });

    it('returns PullRequestBuilderInterface from create method', function () {
        $reflection = new ReflectionClass(PullRequestManagerInterface::class);
        $method = $reflection->getMethod('create');

        expect($method->getReturnType()?->getName())->toBe(PullRequestBuilderInterface::class);
    });
});

describe('PullRequestBuilderInterface', function () {
    it('defines all required builder methods', function () {
        $reflection = new ReflectionClass(PullRequestBuilderInterface::class);

        expect($reflection->hasMethod('title'))->toBeTrue()
            ->and($reflection->hasMethod('body'))->toBeTrue()
            ->and($reflection->hasMethod('head'))->toBeTrue()
            ->and($reflection->hasMethod('base'))->toBeTrue()
            ->and($reflection->hasMethod('draft'))->toBeTrue()
            ->and($reflection->hasMethod('maintainerCanModify'))->toBeTrue()
            ->and($reflection->hasMethod('create'))->toBeTrue();
    });

    it('has chainable methods returning self', function () {
        $reflection = new ReflectionClass(PullRequestBuilderInterface::class);

        $title = $reflection->getMethod('title');
        expect($title->getReturnType()?->getName())->toBe('self');

        $body = $reflection->getMethod('body');
        expect($body->getReturnType()?->getName())->toBe('self');

        $head = $reflection->getMethod('head');
        expect($head->getReturnType()?->getName())->toBe('self');
    });
});

describe('ReviewBuilderInterface', function () {
    it('defines all required review builder methods', function () {
        $reflection = new ReflectionClass(ReviewBuilderInterface::class);

        expect($reflection->hasMethod('approve'))->toBeTrue()
            ->and($reflection->hasMethod('requestChanges'))->toBeTrue()
            ->and($reflection->hasMethod('comment'))->toBeTrue()
            ->and($reflection->hasMethod('addInlineComment'))->toBeTrue()
            ->and($reflection->hasMethod('addSuggestion'))->toBeTrue()
            ->and($reflection->hasMethod('submit'))->toBeTrue();
    });

    it('has chainable methods returning self', function () {
        $reflection = new ReflectionClass(ReviewBuilderInterface::class);

        $approve = $reflection->getMethod('approve');
        expect($approve->getReturnType()?->getName())->toBe('self');

        $comment = $reflection->getMethod('comment');
        expect($comment->getReturnType()?->getName())->toBe('self');
    });
});

describe('ReviewQueryInterface', function () {
    it('defines all required review query methods', function () {
        $reflection = new ReflectionClass(ReviewQueryInterface::class);

        expect($reflection->hasMethod('get'))->toBeTrue()
            ->and($reflection->hasMethod('whereApproved'))->toBeTrue()
            ->and($reflection->hasMethod('whereChangesRequested'))->toBeTrue()
            ->and($reflection->hasMethod('wherePending'))->toBeTrue()
            ->and($reflection->hasMethod('byUser'))->toBeTrue()
            ->and($reflection->hasMethod('latest'))->toBeTrue();
    });
});

describe('CheckRunQueryInterface', function () {
    it('defines all required check run query methods', function () {
        $reflection = new ReflectionClass(CheckRunQueryInterface::class);

        expect($reflection->hasMethod('get'))->toBeTrue()
            ->and($reflection->hasMethod('wherePassing'))->toBeTrue()
            ->and($reflection->hasMethod('whereFailing'))->toBeTrue()
            ->and($reflection->hasMethod('wherePending'))->toBeTrue()
            ->and($reflection->hasMethod('whereNeutral'))->toBeTrue()
            ->and($reflection->hasMethod('whereSkipped'))->toBeTrue()
            ->and($reflection->hasMethod('latest'))->toBeTrue()
            ->and($reflection->hasMethod('byName'))->toBeTrue()
            ->and($reflection->hasMethod('summary'))->toBeTrue();
    });
});

describe('MergeManagerInterface', function () {
    it('defines all required merge manager methods', function () {
        $reflection = new ReflectionClass(MergeManagerInterface::class);

        expect($reflection->hasMethod('merge'))->toBeTrue()
            ->and($reflection->hasMethod('squash'))->toBeTrue()
            ->and($reflection->hasMethod('rebase'))->toBeTrue()
            ->and($reflection->hasMethod('canMerge'))->toBeTrue()
            ->and($reflection->hasMethod('deleteBranch'))->toBeTrue();
    });
});

describe('FileQueryInterface', function () {
    it('defines all required file query methods', function () {
        $reflection = new ReflectionClass(FileQueryInterface::class);

        expect($reflection->hasMethod('get'))->toBeTrue()
            ->and($reflection->hasMethod('whereAdded'))->toBeTrue()
            ->and($reflection->hasMethod('whereModified'))->toBeTrue()
            ->and($reflection->hasMethod('whereRemoved'))->toBeTrue()
            ->and($reflection->hasMethod('whereRenamed'))->toBeTrue()
            ->and($reflection->hasMethod('wherePath'))->toBeTrue()
            ->and($reflection->hasMethod('whereExtension'))->toBeTrue()
            ->and($reflection->hasMethod('stats'))->toBeTrue();
    });
});

describe('CommentManagerInterface', function () {
    it('defines all required comment manager methods', function () {
        $reflection = new ReflectionClass(CommentManagerInterface::class);

        expect($reflection->hasMethod('get'))->toBeTrue()
            ->and($reflection->hasMethod('create'))->toBeTrue()
            ->and($reflection->hasMethod('update'))->toBeTrue()
            ->and($reflection->hasMethod('delete'))->toBeTrue();
    });
});

describe('Contract Type Safety', function () {
    it('ensures ReviewQueryInterface methods return Collection', function () {
        $reflection = new ReflectionClass(ReviewQueryInterface::class);

        $get = $reflection->getMethod('get');
        expect($get->getReturnType()?->getName())->toBe('Illuminate\Support\Collection');

        $whereApproved = $reflection->getMethod('whereApproved');
        expect($whereApproved->getReturnType()?->getName())->toBe('Illuminate\Support\Collection');
    });

    it('ensures nullable return types are properly defined', function () {
        $reflection = new ReflectionClass(PullRequestQueryInterface::class);
        $method = $reflection->getMethod('first');
        $returnType = $method->getReturnType();

        expect($returnType)->not->toBeNull()
            ->and($returnType->allowsNull())->toBeTrue();
    });
});
