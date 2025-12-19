<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\PullRequest;

/**
 * Contract for creating pull requests
 */
interface PullRequestBuilderInterface
{
    public function title(string $title): self;

    public function body(string $body): self;

    public function head(string $branch): self;

    public function base(string $branch): self;

    public function draft(bool $draft = true): self;

    public function maintainerCanModify(bool $allowed = true): self;

    public function create(): PullRequest;
}
