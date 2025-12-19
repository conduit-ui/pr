<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\Comment;
use Illuminate\Support\Collection;

/**
 * Contract for managing comments
 */
interface CommentManagerInterface
{
    /**
     * @return Collection<int, Comment>
     */
    public function get(): Collection;

    public function create(string $body): Comment;

    public function update(int $commentId, string $body): Comment;

    public function delete(int $commentId): bool;
}
