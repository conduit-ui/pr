<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetIssueComments extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $number,
        protected int $perPage = 100,
        protected int $page = 1,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->number}/comments?per_page={$this->perPage}&page={$this->page}";
    }
}
