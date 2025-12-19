<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\CheckRun;
use ConduitUI\Pr\DataTransferObjects\CheckSummary;
use Illuminate\Support\Collection;

/**
 * Contract for querying check runs
 */
interface CheckRunQueryInterface
{
    /**
     * @return Collection<int, CheckRun>
     */
    public function get(): Collection;

    /**
     * @return Collection<int, CheckRun>
     */
    public function wherePassing(): Collection;

    /**
     * @return Collection<int, CheckRun>
     */
    public function whereFailing(): Collection;

    /**
     * @return Collection<int, CheckRun>
     */
    public function wherePending(): Collection;

    /**
     * @return Collection<int, CheckRun>
     */
    public function whereNeutral(): Collection;

    /**
     * @return Collection<int, CheckRun>
     */
    public function whereSkipped(): Collection;

    public function latest(): ?CheckRun;

    public function byName(string $name): ?CheckRun;

    public function summary(): CheckSummary;
}
