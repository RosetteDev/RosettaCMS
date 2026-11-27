<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Socialite Providers
    |--------------------------------------------------------------------------
    |
    | The socialite providers used by the application. These are typically
    | filled in via the .env file so credentials never live in the repo.
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL', null),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URL', null),
    ],

];
