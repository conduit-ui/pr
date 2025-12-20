<?php

declare(strict_types=1);

use ConduitUI\Pr\Requests\CreateMilestone;
use ConduitUI\Pr\Requests\DeleteMilestone;
use ConduitUI\Pr\Requests\GetMilestone;
use ConduitUI\Pr\Requests\ListMilestones;
use ConduitUI\Pr\Requests\UpdateMilestone;

it('GetMilestone has correct endpoint', function () {
    $request = new GetMilestone('owner', 'repo', 5);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/milestones/5');
});

it('ListMilestones has correct endpoint', function () {
    $request = new ListMilestones('owner', 'repo', 'all');

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/milestones');
});

it('ListMilestones has correct query parameters for open state', function () {
    $request = new ListMilestones('owner', 'repo', 'open');
    $reflection = new ReflectionClass($request);
    $method = $reflection->getMethod('defaultQuery');

    $query = $method->invoke($request);

    expect($query)->toBe(['state' => 'open']);
});

it('ListMilestones has correct query parameters for closed state', function () {
    $request = new ListMilestones('owner', 'repo', 'closed');
    $reflection = new ReflectionClass($request);
    $method = $reflection->getMethod('defaultQuery');

    $query = $method->invoke($request);

    expect($query)->toBe(['state' => 'closed']);
});

it('CreateMilestone has correct endpoint', function () {
    $request = new CreateMilestone('owner', 'repo', ['title' => 'v1.0']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/milestones');
});

it('CreateMilestone has correct body', function () {
    $data = [
        'title' => 'v1.0',
        'description' => 'First release',
        'due_on' => '2025-12-31T23:59:59Z',
        'state' => 'open',
    ];
    $request = new CreateMilestone('owner', 'repo', $data);
    $reflection = new ReflectionClass($request);
    $method = $reflection->getMethod('defaultBody');

    $body = $method->invoke($request);

    expect($body)->toBe($data);
});

it('UpdateMilestone has correct endpoint', function () {
    $request = new UpdateMilestone('owner', 'repo', 5, ['title' => 'v1.0']);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/milestones/5');
});

it('UpdateMilestone has correct body', function () {
    $data = ['title' => 'v1.0 Updated'];
    $request = new UpdateMilestone('owner', 'repo', 5, $data);
    $reflection = new ReflectionClass($request);
    $method = $reflection->getMethod('defaultBody');

    $body = $method->invoke($request);

    expect($body)->toBe($data);
});

it('DeleteMilestone has correct endpoint', function () {
    $request = new DeleteMilestone('owner', 'repo', 5);

    expect($request->resolveEndpoint())->toBe('/repos/owner/repo/milestones/5');
});
