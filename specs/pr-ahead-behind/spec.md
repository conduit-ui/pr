# SPEC: pr-ahead-behind — branch comparison (ahead_by / behind_by)

Status: **READY** (night-shift 2026-08-31)
Repo: `conduit-ui/pr`
Issue: [#40](https://github.com/conduit-ui/pr/issues/40)
Thor clone: `/home/jordan/Projects/conduit-ui/repos/pr`
Slug: `pr-ahead-behind`

**Story:** PR automation needs to know when a head is behind its base (rebase nudge, hygiene). The package has `PullRequest::query()`, `head->ref`, `base->ref`, but no compare. GitHub: `GET /repos/{owner}/{repo}/compare/{basehead}`.

## Goal

Expose ahead/behind/status for a PR (and for two arbitrary refs) via a small Comparison DTO. Night-shift types the Saloon request + wrapper methods + Pest. No live GitHub.

## In-scope

- `GET /repos/{owner}/{repo}/compare/{base}...{head}` Saloon request (same style as `GetPullRequest`).
- DTO `Comparison` (or `BranchComparison`) with `aheadBy: int`, `behindBy: int`, `status: string` (`ahead` | `behind` | `diverged` | `identical`). Map from `ahead_by`, `behind_by`, `status`. Do **not** hydrate `commits[]` / `files[]`.
- Instance: `$pr->compare()` uses this PR's `base.ref` vs `head.ref` (and owner/repo from the wrapper).
- Static/service: `PullRequests::compareBranches('owner/repo', 'main', 'feature-branch')` (or equivalent on the wrapper) for arbitrary refs.
- Pest: request endpoint; DTO fromArray; fake connector returns compare JSON; no network.

## Out-of-scope

- Listing files/commits from the compare payload.
- Auto-rebase, auto-nudge, scheduled hygiene agents.
- GraphQL.
- PSTrax. Merge. Secrets. `gh pr merge`.

## Files likely touched

```
src/Requests/GetCompare.php
src/DataTransferObjects/Comparison.php
src/PullRequest.php
src/PullRequests.php
tests/Unit/RequestsTest.php
tests/Unit/PullRequestWrapperTest.php
tests/Unit/DataTransferObjects/
specs/pr-ahead-behind/
```

## Tests

```
vendor/bin/pest --filter='Compare|Comparison'
```

Also keep `vendor/bin/pest --filter=RequestsTest` green if you add the endpoint assertion there.

Required:

- `GetCompare` resolves `/repos/{owner}/{repo}/compare/{base}...{head}`.
- `Comparison::fromArray` maps `ahead_by` / `behind_by` / `status`.
- Wrapper `compare()` returns the DTO from a faked response (`aheadBy=1`, `behindBy=3`, `status=diverged`).
- `compareBranches` hits the same request with explicit refs.
- No live GitHub.

## Done-when

- [ ] `$pr->compare()->aheadBy` / `behindBy` / `status` work from fakes.
- [ ] Arbitrary branch compare exists.
- [ ] Filter green. Allowlist only.

## Must-not

- No PSTrax.
- No merge (`gh pr merge`).
- No secrets.
- Do not pull the full compare `files`/`commits` arrays into the DTO.
- Do not add rebase/merge helpers in this SPEC.

## Night-shift

Local (qwen-coder-32k) types this. Isolated branch. No merge from the worker.
