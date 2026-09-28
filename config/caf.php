<?php

use App\Enums\SeasonState;

return [

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    */

    'brand' => [
        'name' => env('CAF_BRAND_NAME', 'CAF Management'),
        'short' => env('CAF_BRAND_SHORT', 'CAF'),
        'tagline' => env('CAF_BRAND_TAGLINE', 'Culture Acapella Festival'),
        'site_url' => env('APP_URL', 'http://localhost'),
        'contact_email' => env('CAF_CONTACT_EMAIL', 'hello@cultureacapellafestival.com'),
        'contact_phone' => env('CAF_CONTACT_PHONE', '+255 754 000 000'),
        'address' => env('CAF_ADDRESS', 'Kigamboni, Dar es Salaam, Tanzania'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Registration codes
    |--------------------------------------------------------------------------
    |
    | Codes look like CAF3-0042, where the leading number is the season
    | number. Lookup on the public status page is case-insensitive.
    |
    */

    'registration' => [
        'code_prefix' => 'CAF',
        'code_padding' => 4,
        'days_before_early_bird_closes' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | The management system uses the framework's session guard plus an
    | optional second factor. A user must have the "admin.access"
    | permission to reach /admin; a pending second-factor challenge is
    | held in the session until it is verified.
    |
    */

    'auth' => [
        'throttle_attempts' => (int) env('CAF_LOGIN_MAX_ATTEMPTS', 5),
        'throttle_decay_seconds' => (int) env('CAF_LOGIN_DECAY_SECONDS', 60),
        'two_factor_session_key' => 'caf.two_factor_user_id',
        'two_factor_reserve_key' => 'caf.two_factor_reserve',
        'two_factor_issuer' => env('CAF_TWO_FACTOR_ISSUER', 'CAF Management'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default season
    |--------------------------------------------------------------------------
    |
    | Used when no season has been selected in the session yet, and as the
    | fallback when a session points at a season that no longer exists.
    |
    */

    'default_season_state' => SeasonState::Live->value,

    /*
    |--------------------------------------------------------------------------
    | Audit log
    |--------------------------------------------------------------------------
    */

    'audit' => [
        'record_ip' => true,
        'record_user_agent' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin navigation
    |--------------------------------------------------------------------------
    |
    | Every entry declares the permission a user needs before the link is
    | rendered. Badges are resolved by the badge closures so the counts
    | always reflect the active season.
    |
    */

    'nav' => [

        [
            'label' => 'Overview',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'grid', 'permission' => 'dashboard.view'],
            ],
        ],

        [
            'label' => 'Operations',
            'items' => [
                [
                    'label' => 'Registrations',
                    'route' => 'admin.registrations.index',
                    'icon' => 'file',
                    'permission' => 'registrations.view',
                    'badge' => 'needs_attention',
                ],
                [
                    'label' => 'Judging & Review',
                    'route' => 'admin.judging.index',
                    'icon' => 'star',
                    'permission' => 'judging.view',
                    'badge' => 'pending_scores',
                ],
                [
                    'label' => 'Payments',
                    'route' => 'admin.payments.index',
                    'icon' => 'card',
                    'permission' => 'payments.view',
                    'badge' => 'unsettled_invoices',
                ],
                [
                    'label' => 'Communications',
                    'route' => 'admin.communications.index',
                    'icon' => 'message',
                    'permission' => 'communications.view',
                    'badge' => 'open_threads',
                ],
            ],
        ],

        [
            'label' => 'Insight',
            'items' => [
                ['label' => 'Analytics', 'route' => 'admin.analytics.index', 'icon' => 'chart', 'permission' => 'analytics.view'],
                ['label' => 'Audit Log', 'route' => 'admin.audit.index', 'icon' => 'shield', 'permission' => 'audit.view'],
            ],
        ],

        [
            'label' => 'Manage',
            'items' => [
                ['label' => 'Content & Programme', 'route' => 'admin.content.index', 'icon' => 'layers', 'permission' => 'content.view'],
                ['label' => 'Group Portal', 'route' => 'admin.portal.index', 'icon' => 'users', 'permission' => 'portal.view'],
                ['label' => 'Teams', 'route' => 'admin.teams.index', 'icon' => 'users', 'permission' => 'teams.view'],
                ['label' => 'Settings', 'route' => 'admin.settings.index', 'icon' => 'sliders', 'permission' => 'settings.view'],
            ],
        ],

    ],

];
