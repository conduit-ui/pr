<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your GitHub access token for pull request operations.
    | You can set this in your .env file as GITHUB_TOKEN or configure
    | it in config/services.php under 'github.token'.
    |
    */

    'github' => [
        'token' => env('GITHUB_TOKEN'),
    ],
];
