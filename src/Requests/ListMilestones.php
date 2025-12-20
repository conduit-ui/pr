<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListMilestones extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected string $state = 'all',
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/milestones";
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return ['state' => $this->state];
    }
}
