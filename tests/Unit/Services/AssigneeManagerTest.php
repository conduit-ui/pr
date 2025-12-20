<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\DataTransferObjects\User;
use ConduitUI\Pr\Requests\AddAssignees;
use ConduitUI\Pr\Requests\RemoveAssignees;
use ConduitUI\Pr\Services\AssigneeManager;
use Saloon\Http\Request;
use Saloon\Http\Response;

class MockAssigneeManagerResponse extends Response
{
    public function __construct(private array $data = [])
    {
        // Skip parent constructor
    }

    public function json(string|int|null $key = null, mixed $default = null): mixed
    {
        if ($key !== null) {
            return $this->data[$key] ?? $default;
        }

        return $this->data;
    }

    public function successful(): bool
    {
        return true;
    }
}

class AssigneeManagerTestConnector extends Connector
{
    public ?Request $lastRequest = null;

    public function __construct(private array $assignees = [])
    {
        parent::__construct('test-token');
    }

    public function send(Request $request, ...$args): Response
    {
        $this->lastRequest = $request;

        return new MockAssigneeManagerResponse([
            'assignees' => $this->assignees,
        ]);
    }
}

it('can get assignees', function () {
    $connector = new AssigneeManagerTestConnector([
        ['id' => 1, 'login' => 'user1', 'avatar_url' => 'https://example.com/1.jpg', 'html_url' => 'https://github.com/user1', 'type' => 'User'],
        ['id' => 2, 'login' => 'user2', 'avatar_url' => 'https://example.com/2.jpg', 'html_url' => 'https://github.com/user2', 'type' => 'User'],
    ]);

    $manager = new AssigneeManager($connector, 'test/repo', 123);
    $assignees = $manager->get();

    expect($assignees)->toHaveCount(2)
        ->and($assignees->first())->toBeInstanceOf(User::class)
        ->and($assignees->first()->login)->toBe('user1')
        ->and($assignees->last()->login)->toBe('user2');
});

it('can add single assignee', function () {
    $connector = new AssigneeManagerTestConnector;

    $manager = new AssigneeManager($connector, 'test/repo', 123);
    $result = $manager->add('newuser');

    expect($result)->toBeInstanceOf(AssigneeManager::class)
        ->and($connector->lastRequest)->toBeInstanceOf(AddAssignees::class);
});

it('can add multiple assignees', function () {
    $connector = new AssigneeManagerTestConnector;

    $manager = new AssigneeManager($connector, 'test/repo', 123);
    $result = $manager->addMany(['user1', 'user2', 'user3']);

    expect($result)->toBeInstanceOf(AssigneeManager::class)
        ->and($connector->lastRequest)->toBeInstanceOf(AddAssignees::class);
});

it('can remove single assignee', function () {
    $connector = new AssigneeManagerTestConnector;

    $manager = new AssigneeManager($connector, 'test/repo', 123);
    $result = $manager->remove('user1');

    expect($result)->toBeInstanceOf(AssigneeManager::class)
        ->and($connector->lastRequest)->toBeInstanceOf(RemoveAssignees::class);
});

it('can remove multiple assignees', function () {
    $connector = new AssigneeManagerTestConnector;

    $manager = new AssigneeManager($connector, 'test/repo', 123);
    $result = $manager->removeMany(['user1', 'user2']);

    expect($result)->toBeInstanceOf(AssigneeManager::class)
        ->and($connector->lastRequest)->toBeInstanceOf(RemoveAssignees::class);
});

it('can replace assignees', function () {
    $connector = new AssigneeManagerTestConnector([
        ['id' => 1, 'login' => 'olduser', 'avatar_url' => 'https://example.com/1.jpg', 'html_url' => 'https://github.com/olduser', 'type' => 'User'],
    ]);

    $manager = new AssigneeManager($connector, 'test/repo', 123);
    $result = $manager->replace(['newuser1', 'newuser2']);

    expect($result)->toBeInstanceOf(AssigneeManager::class);
});

it('can replace empty assignees', function () {
    $connector = new AssigneeManagerTestConnector;

    $manager = new AssigneeManager($connector, 'test/repo', 123);
    $result = $manager->replace(['newuser1', 'newuser2']);

    expect($result)->toBeInstanceOf(AssigneeManager::class)
        ->and($connector->lastRequest)->toBeInstanceOf(AddAssignees::class);
});

it('can clear assignees', function () {
    $connector = new AssigneeManagerTestConnector([
        ['id' => 1, 'login' => 'user1', 'avatar_url' => 'https://example.com/1.jpg', 'html_url' => 'https://github.com/user1', 'type' => 'User'],
    ]);

    $manager = new AssigneeManager($connector, 'test/repo', 123);
    $result = $manager->clear();

    expect($result)->toBeInstanceOf(AssigneeManager::class);
});

it('clear does nothing when no assignees exist', function () {
    $connector = new AssigneeManagerTestConnector([]);

    $manager = new AssigneeManager($connector, 'test/repo', 123);
    $result = $manager->clear();

    expect($result)->toBeInstanceOf(AssigneeManager::class);
});

it('can check if user is assigned', function () {
    $connector = new AssigneeManagerTestConnector([
        ['id' => 1, 'login' => 'user1', 'avatar_url' => 'https://example.com/1.jpg', 'html_url' => 'https://github.com/user1', 'type' => 'User'],
        ['id' => 2, 'login' => 'user2', 'avatar_url' => 'https://example.com/2.jpg', 'html_url' => 'https://github.com/user2', 'type' => 'User'],
    ]);

    $manager = new AssigneeManager($connector, 'test/repo', 123);

    expect($manager->has('user1'))->toBeTrue()
        ->and($manager->has('user2'))->toBeTrue()
        ->and($manager->has('user3'))->toBeFalse();
});
