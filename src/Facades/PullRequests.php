<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Facades;

use ConduitUI\Pr\PullRequest;
use ConduitUI\Pr\QueryBuilder;
use Illuminate\Support\Facades\Facade;

/**
 * @method static QueryBuilder for(string $repository)
 * @method static PullRequest find(string $repository, int $number)
 * @method static PullRequest create(string $repository, array $attributes)
 * @method static QueryBuilder query()
 * @method static QueryBuilder open(string $repository)
 * @method static QueryBuilder closed(string $repository)
 * @method static QueryBuilder merged(string $repository)
 *
 * @see \ConduitUI\Pr\PullRequests
 */
class PullRequests extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \ConduitUI\Pr\PullRequests::class;
    }
}
