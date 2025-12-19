<?php

declare(strict_types=1);

namespace ConduitUI\Pr;

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Pr\Contracts\PrServiceInterface;
use ConduitUI\Pr\Services\GitHubPrService;
use Illuminate\Support\ServiceProvider;

class PrServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/pr.php',
            'pr'
        );

        $this->app->singleton(PrServiceInterface::class, function ($app) {
            $token = $this->resolveToken($app);

            if ($token === null) {
                throw new \RuntimeException(
                    'GitHub token not configured. Set GITHUB_TOKEN environment variable or publish and configure the pr.php config file.'
                );
            }

            return new GitHubPrService(new Connector($token));
        });

        $this->app->singleton(GitHubPrService::class, function ($app) {
            return $app->make(PrServiceInterface::class);
        });

        // Register PullRequests for facade support
        $this->app->singleton(PullRequests::class, function ($app) {
            // Return a proxy that allows both static and instance usage
            return new class($app->make(PrServiceInterface::class))
            {
                public function __construct(private PrServiceInterface $service)
                {
                    PullRequests::setService($this->service);
                }

                public function __call(string $method, array $arguments): mixed
                {
                    return PullRequests::$method(...$arguments);
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/pr.php' => config_path('pr.php'),
            ], 'pr-config');
        }

        // Auto-configure the PullRequests facade only if token is available
        // This prevents crashing apps that don't need PR functionality
        if ($this->resolveToken($this->app) !== null) {
            PullRequests::setService($this->app->make(PrServiceInterface::class));
        }
    }

    /**
     * Resolve the GitHub token from available configuration sources.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     */
    private function resolveToken($app): ?string
    {
        $sources = [
            $app['config']->get('pr.github.token'),
            $app['config']->get('services.github.token'),
            env('GITHUB_TOKEN'),
        ];

        foreach ($sources as $token) {
            if (is_string($token) && trim($token) !== '') {
                return trim($token);
            }
        }

        return null;
    }
}
