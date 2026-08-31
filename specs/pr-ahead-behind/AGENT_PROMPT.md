Implement SPEC `specs/pr-ahead-behind/spec.md` exactly. Night-shift. Allowlist only. Pest first.

You are a local Ollama coding agent (qwen-coder-32k). You do not merge.

Do:
1. Read the spec, `src/PullRequest.php`, `src/PullRequests.php`, `src/Requests/GetPullRequest.php`, `tests/Unit/RequestsTest.php`.
2. Add `GetCompare` request, `Comparison` DTO (`aheadBy`, `behindBy`, `status` only), `$pr->compare()`, and `PullRequests::compareBranches`.
3. Pest with Saloon fakes. No live GitHub. Do not map commits/files.
4. `vendor/bin/pest --filter='Compare|Comparison'` green. Stop.

Do not: PSTrax, `gh pr merge`, rebase automation, secrets, deploy, GraphQL.
