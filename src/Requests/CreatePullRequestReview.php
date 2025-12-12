<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreatePullRequestReview extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $number,
        protected string $event,
        protected ?string $body = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/pulls/{$this->number}/reviews";
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        $payload = ['event' => $this->event];

        if ($this->body !== null) {
            $payload['body'] = $this->body;
        }

        return $payload;
    }
}
