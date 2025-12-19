<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\Review;

interface ReviewBuilderInterface
{
    public function approve(?string $comment = null): self;

    public function requestChanges(?string $comment = null): self;

    public function comment(string $body): self;

    public function addInlineComment(string $path, int $line, string $comment): self;

    public function addSuggestion(string $path, int $startLine, int $endLine, string $suggestion): self;

    public function submit(): Review;
}
