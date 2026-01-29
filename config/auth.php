<?php

return [

    'defaults' => [
        'guard' => 'web', // Default guard (for regular users)
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
            'session_name' => 'laravel_session_admin', // Separate session name for admin
        ],

        'voter' => [ 
            'driver' => 'session',
            'provider' => 'voters',
            'session_name' => 'laravel_session_voter', // Separate session name for voters
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'voters' => [
            'driver' => 'eloquent',
            'model' => App\Models\Voter::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Reset
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        
        'voters' => [
            'provider' => 'voters',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];