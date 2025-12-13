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

    /**
     * @param  array<int, array{path: string, line: int, body: string}>  $comments
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $number,
        protected string $event,
        protected ?string $reviewBody = null,
        protected array $comments = [],
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

        if ($this->reviewBody !== null) {
            $payload['body'] = $this->reviewBody;
        }

        if ($this->comments !== []) {
            $payload['comments'] = $this->comments;
        }

        return $payload;
    }
}
