<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreatePullRequestComment extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $number,
        protected string $body,
        protected string $path,
        protected int $line,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/pulls/{$this->number}/comments";
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'body' => $this->body,
            'path' => $this->path,
            'line' => $this->line,
        ];
    }
}
