<?php

declare(strict_types=1);

namespace ConduitUI\Pr;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\Contracts\PullRequestQueryInterface;
use ConduitUI\Pr\DataTransferObjects\PullRequest as PullRequestData;
use ConduitUI\Pr\Requests\ListPullRequests;

class QueryBuilder implements PullRequestQueryInterface
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

    /**
     * Alias for label()
     */
    public function whereLabel(string $label): self
    {
        return $this->label($label);
    }

    /**
     * Filter by multiple labels (match any)
     *
     * @param  array<int, string>  $labels
     */
    public function whereLabels(array $labels): self
    {
        $this->filters['labels'] = implode(',', $labels);

        return $this;
    }

    /**
     * Filter by multiple labels (match all) - client-side filter
     *
     * @param  array<int, string>  $labels
     */
    public function whereAllLabels(array $labels): self
    {
        $this->filters['_all_labels'] = $labels;

        return $this;
    }

    public function whereMerged(): self
    {
        // Note: GitHub API doesn't have a direct 'merged' filter
        // This will need to be filtered client-side after fetching
        $this->filters['_merged'] = true;

        return $this;
    }

    public function whereDraft(): self
    {
        // Note: GitHub API doesn't have a direct 'draft' filter
        // This will need to be filtered client-side after fetching
        $this->filters['_draft'] = true;

        return $this;
    }

    public function whereBase(string $branch): self
    {
        $this->filters['base'] = $branch;

        return $this;
    }

    public function whereHead(string $branch): self
    {
        $this->filters['head'] = $branch;

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

        // Separate client-side filters from API filters
        $clientSideFilters = [];
        $apiFilters = [];

        foreach ($this->filters as $key => $value) {
            if (str_starts_with($key, '_')) {
                $clientSideFilters[$key] = $value;
            } else {
                $apiFilters[$key] = $value;
            }
        }

        $params = array_merge($apiFilters, [
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

        $results = array_map(
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
        );

        // Apply client-side filters
        if (isset($clientSideFilters['_merged'])) {
            $results = array_filter($results, fn (PullRequest $pr) => $pr->data->isMerged());
        }

        if (isset($clientSideFilters['_draft'])) {
            $results = array_filter($results, fn (PullRequest $pr) => $pr->data->isDraft());
        }

        if (isset($clientSideFilters['_all_labels'])) {
            $requiredLabels = $clientSideFilters['_all_labels'];
            $results = array_filter($results, function (PullRequest $pr) use ($requiredLabels): bool {
                $prLabels = array_map(fn ($label) => $label->name ?? '', $pr->data->labels ?? []); // @phpstan-ignore-line

                foreach ($requiredLabels as $required) {
                    if (! in_array($required, $prLabels, true)) {
                        return false;
                    }
                }

                return true;
            });
        }

        return array_values($results);
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

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Pluck a single column from results
     *
     * @return array<int, mixed>
     */
    public function pluck(string $key): array
    {
        return array_map(
            fn (PullRequest $pr) => $pr->data->{$key} ?? null, // @phpstan-ignore-line
            $this->get()
        );
    }

    /**
     * Alias methods for state filters
     */
    public function whereOpen(): self
    {
        return $this->open();
    }

    public function whereClosed(): self
    {
        return $this->closed();
    }

    public function whereState(string $state): self
    {
        return $this->state($state);
    }

    public function whereAuthor(string $author): self
    {
        return $this->author($author);
    }

    /**
     * Ordering shortcuts
     */
    public function orderByCreated(string $direction = 'desc'): self
    {
        return $this->orderBy('created', $direction);
    }

    public function orderByUpdated(string $direction = 'desc'): self
    {
        return $this->orderBy('updated', $direction);
    }

    public function orderByPopularity(): self
    {
        return $this->orderBy('popularity', 'desc');
    }

    public function orderByLongRunning(): self
    {
        return $this->orderBy('long-running', 'asc');
    }

    /**
     * Pagination shortcuts
     */
    public function perPage(int $count): self
    {
        return $this->take($count);
    }

    /**
     * Alias for repository()
     */
    public function repo(string $repository): self
    {
        return $this->repository($repository);
    }
}
