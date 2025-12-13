<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListPullRequests extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected array $filters = [],
    ) {}

    public function resolveEndpoint(): string
    {
        $query = http_build_query($this->filters);

        return "/repos/{$this->owner}/{$this->repo}/pulls".($query !== '' ? "?{$query}" : '');
    }
}
