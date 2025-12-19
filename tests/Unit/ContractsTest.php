<?php

declare(strict_types=1);

use ConduitUI\Pr\Contracts\CheckRunQueryInterface;
use ConduitUI\Pr\Contracts\CommentManagerInterface;
use ConduitUI\Pr\Contracts\FileQueryInterface;
use ConduitUI\Pr\Contracts\MergeManagerInterface;
use ConduitUI\Pr\Contracts\PullRequestActionsInterface;
use ConduitUI\Pr\Contracts\PullRequestBuilderInterface;
use ConduitUI\Pr\Contracts\PullRequestManagerInterface;
use ConduitUI\Pr\Contracts\PullRequestQueryInterface;
use ConduitUI\Pr\Contracts\ReviewBuilderInterface;
use ConduitUI\Pr\Contracts\ReviewQueryInterface;

describe('PullRequestQueryInterface', function () {
    it('defines all required query methods', function () {
        $reflection = new ReflectionClass(PullRequestQueryInterface::class);

        expect($reflection->hasMethod('whereOpen'))->toBeTrue()
            ->and($reflection->hasMethod('whereClosed'))->toBeTrue()
            ->and($reflection->hasMethod('whereMerged'))->toBeTrue()
            ->and($reflection->hasMethod('whereState'))->toBeTrue()
            ->and($reflection->hasMethod('whereBase'))->toBeTrue()
            ->and($reflection->hasMethod('whereHead'))->toBeTrue()
            ->and($reflection->hasMethod('whereAuthor'))->toBeTrue()
            ->and($reflection->hasMethod('whereLabel'))->toBeTrue()
            ->and($reflection->hasMethod('whereLabels'))->toBeTrue()
            ->and($reflection->hasMethod('whereDraft'))->toBeTrue()
            ->and($reflection->hasMethod('orderByCreated'))->toBeTrue()
            ->and($reflection->hasMethod('orderByUpdated'))->toBeTrue()
            ->and($reflection->hasMethod('perPage'))->toBeTrue()
            ->and($reflection->hasMethod('page'))->toBeTrue()
            ->and($reflection->hasMethod('get'))->toBeTrue()
            ->and($reflection->hasMethod('first'))->toBeTrue()
            ->and($reflection->hasMethod('count'))->toBeTrue()
            ->and($reflection->hasMethod('exists'))->toBeTrue();
    });

    it('has chainable where methods returning self', function () {
        $reflection = new ReflectionClass(PullRequestQueryInterface::class);

        $whereOpen = $reflection->getMethod('whereOpen');
        expect($whereOpen->getReturnType()?->getName())->toBe('self');

        $whereClosed = $reflection->getMethod('whereClosed');
        expect($whereClosed->getReturnType()?->getName())->toBe('self');

        $whereState = $reflection->getMethod('whereState');
        expect($whereState->getReturnType()?->getName())->toBe('self');
    });
});

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

describe('PullRequestActionsInterface', function () {
    it('defines all required action methods', function () {
        $reflection = new ReflectionClass(PullRequestActionsInterface::class);

        expect($reflection->hasMethod('close'))->toBeTrue()
            ->and($reflection->hasMethod('reopen'))->toBeTrue()
            ->and($reflection->hasMethod('markDraft'))->toBeTrue()
            ->and($reflection->hasMethod('markReady'))->toBeTrue()
            ->and($reflection->hasMethod('addLabel'))->toBeTrue()
            ->and($reflection->hasMethod('addLabels'))->toBeTrue()
            ->and($reflection->hasMethod('removeLabel'))->toBeTrue()
            ->and($reflection->hasMethod('setLabels'))->toBeTrue()
            ->and($reflection->hasMethod('requestReview'))->toBeTrue()
            ->and($reflection->hasMethod('requestReviews'))->toBeTrue()
            ->and($reflection->hasMethod('requestTeamReview'))->toBeTrue()
            ->and($reflection->hasMethod('assign'))->toBeTrue()
            ->and($reflection->hasMethod('unassign'))->toBeTrue()
            ->and($reflection->hasMethod('comment'))->toBeTrue();
    });

    it('has chainable methods returning self', function () {
        $reflection = new ReflectionClass(PullRequestActionsInterface::class);

        $addLabel = $reflection->getMethod('addLabel');
        expect($addLabel->getReturnType()?->getName())->toBe('self');

        $requestReview = $reflection->getMethod('requestReview');
        expect($requestReview->getReturnType()?->getName())->toBe('self');
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
    it('ensures PullRequestQueryInterface get returns Collection', function () {
        $reflection = new ReflectionClass(PullRequestQueryInterface::class);
        $method = $reflection->getMethod('get');
        $returnType = $method->getReturnType();

        expect($returnType)->not->toBeNull()
            ->and($returnType->getName())->toBe('Illuminate\Support\Collection');
    });

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
