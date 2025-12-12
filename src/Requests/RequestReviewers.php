<?php

declare(strict_types=1);

namespace ConduitUI\Pr\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RequestReviewers extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<int, string>  $reviewers
     * @param  array<int, string>  $teamReviewers
     */
    public function __construct(
        protected string $owner,
        protected string $repo,
        protected int $number,
        protected array $reviewers = [],
        protected array $teamReviewers = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/repos/{$this->owner}/{$this->repo}/pulls/{$this->number}/requested_reviewers";
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        $payload = [];

        if (! empty($this->reviewers)) {
            $payload['reviewers'] = $this->reviewers;
        }

        if (! empty($this->teamReviewers)) {
            $payload['team_reviewers'] = $this->teamReviewers;
        }

        return $payload;
    }
}
