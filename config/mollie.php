<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mollie API Key
    |--------------------------------------------------------------------------
    |
    | Your Mollie API key. You can find it in your Mollie Dashboard at
    | https://www.mollie.com/dashboard/developers/api-keys
    |
    */

    'key' => env('MOLLIE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    | Settings for automatic payment synchronization
    |
    */

    'sync' => [
        // How far back to look for payments (in days)
        'lookback_days' => env('MOLLIE_SYNC_LOOKBACK_DAYS', 30),

        // Maximum number of payments to fetch per sync
        'limit' => env('MOLLIE_SYNC_LIMIT', 250),

        // Automatically match payments with invoices
        'auto_match' => env('MOLLIE_AUTO_MATCH', true),

        // Automatically create invoices for unmatched payments
        'auto_create_invoices' => env('MOLLIE_AUTO_CREATE_INVOICES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Project Mapping
    |--------------------------------------------------------------------------
    |
    | Map payment descriptions to projects
    | Use keywords in description to auto-assign project
    |
    */

    'project_mapping' => [
        'herdenkingsportaal' => env('MOLLIE_PROJECT_HERDENKINGSPORTAAL', 1),
        'memorial' => env('MOLLIE_PROJECT_HERDENKINGSPORTAAL', 1),
        'idsee' => env('MOLLIE_PROJECT_IDSEE', 2),
        'judotoernooi' => env('MOLLIE_PROJECT_JUDOTOERNOOI', 3),
        'judo' => env('MOLLIE_PROJECT_JUDOTOERNOOI', 3),
    ],

];
