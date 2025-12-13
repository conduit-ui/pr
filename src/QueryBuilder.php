<?php

declare(strict_types=1);

namespace ConduitUI\Pr;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\Requests\ListPullRequests;

class QueryBuilder
{
    protected ?string $owner = null;

    protected ?string $repo = null;

    /**
     * @var array<string, mixed>
     */
    protected array $filters = [];

    protected string $sort = 'created';

    protected string $direction = 'desc';

    protected ?int $limit = null;

    protected int $page = 1;

    public function __construct(
        protected Connector $connector,
    ) {}

    public function repository(string $repository): self
    {
        [$this->owner, $this->repo] = explode('/', $repository, 2);

        return $this;
    }

    public function state(string $state): self
    {
        $this->filters['state'] = $state;

        return $this;
    }

    public function open(): self
    {
        return $this->state('open');
    }

    public function closed(): self
    {
        return $this->state('closed');
    }

    public function all(): self
    {
        return $this->state('all');
    }

    public function author(string $author): self
    {
        $this->filters['creator'] = $author;

        return $this;
    }

    public function label(string $label): self
    {
        $this->filters['labels'] = $label;

        return $this;
    }

    public function orderBy(string $sort, string $direction = 'desc'): self
    {
        $this->sort = $sort;
        $this->direction = $direction;

        return $this;
    }

    public function take(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function page(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return array<int, PullRequest>
     */
    public function get(): array
    {
        if ($this->owner === null || $this->repo === null) {
            throw new \InvalidArgumentException('Repository is required. Use repository("owner/repo") first.');
        }

        $owner = $this->owner;
        $repo = $this->repo;

        $params = array_merge($this->filters, [
            'sort' => $this->sort,
            'direction' => $this->direction,
            'per_page' => $this->limit ?? 30,
            'page' => $this->page,
        ]);

        if (! isset($params['state'])) {
            $params['state'] = 'open';
        }

        $response = $this->connector->send(new ListPullRequests(
            $owner,
            $repo,
            $params
        ));

        return array_values(array_map(
            /**
             * @param  array<string, mixed>  $data
             */
            fn (mixed $data) => new PullRequest(
                $this->connector,
                $owner,
                $repo,
                PullRequestData::fromArray($data) // @phpstan-ignore-line
            ),
            $response->json()
        ));
    }

    public function first(): ?PullRequest
    {
        $results = $this->take(1)->get();

        return $results[0] ?? null;
    }

    public function count(): int
    {
        return count($this->get());
    }
}
