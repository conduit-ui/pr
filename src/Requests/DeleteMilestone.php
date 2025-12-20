<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteMilestone extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $number,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/milestones/{$this->number}";
    }
}
