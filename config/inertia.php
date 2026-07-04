<?php

return [

    'ssr' => [
        'enabled' => env('INERTIA_SSR_ENABLED', false),
        'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714'),
    ],

    'testing' => [
        'ensure_pages_exist' => true,
        'ensure_assets_have_version' => true,
        'page_paths' => [
            resource_path('js/Pages'),
        ],
        'asset_paths' => [
            resource_path('js'),
            public_path(),
        ],
    ],

];