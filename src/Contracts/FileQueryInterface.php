<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Contracts;

use ConduitUI\Pr\DataTransferObjects\File;
use ConduitUI\Pr\DataTransferObjects\FileStats;
use Illuminate\Support\Collection;

/**
 * Contract for querying changed files
 */
interface FileQueryInterface
{
    /**
     * @return Collection<int, File>
     */
    public function get(): Collection;

    /**
     * @return Collection<int, File>
     */
    public function whereAdded(): Collection;

    /**
     * @return Collection<int, File>
     */
    public function whereModified(): Collection;

    /**
     * @return Collection<int, File>
     */
    public function whereRemoved(): Collection;

    /**
     * @return Collection<int, File>
     */
    public function whereRenamed(): Collection;

    /**
     * @param  string  $pattern  Glob pattern for file paths
     * @return Collection<int, File>
     */
    public function wherePath(string $pattern): Collection;

    /**
     * @return Collection<int, File>
     */
    public function whereExtension(string $extension): Collection;

    public function stats(): FileStats;
}
