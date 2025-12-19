<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\PullRequest;
use Illuminate\Support\Collection;

/**
 * Contract for querying pull requests
 */
interface PullRequestQueryInterface
{
    public function whereOpen(): self;

    public function whereClosed(): self;

    public function whereMerged(): self;

    public function whereState(string $state): self;

    public function whereBase(string $branch): self;

    public function whereHead(string $branch): self;

    public function whereAuthor(string $username): self;

    public function whereLabel(string $label): self;

    /**
     * @param  array<int, string>  $labels
     */
    public function whereLabels(array $labels): self;

    public function whereDraft(bool $draft = true): self;

    public function orderByCreated(string $direction = 'desc'): self;

    public function orderByUpdated(string $direction = 'desc'): self;

    public function perPage(int $count): self;

    public function page(int $page): self;

    /**
     * @return Collection<int, PullRequest>
     */
    public function get(): Collection;

    public function first(): ?PullRequest;

    public function count(): int;

    public function exists(): bool;
}
