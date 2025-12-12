<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AddIssueLabels extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<int, string>  $labels
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $number,
        protected array $labels,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/issues/{$this->number}/labels";
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return ['labels' => $this->labels];
    }
}
