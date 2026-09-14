<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media & LLM API providers
    |--------------------------------------------------------------------------
    | Base URLs and the default model catalogs offered in the influencer wizard.
    | Per-team keys come from the team's provider connections at runtime.
    */

    'fal' => [
        'base_url' => env('FAL_BASE_URL', 'https://queue.fal.run'),
        'models' => [
            'fal-ai/nano-banana-2' => 'Nano Banana 2',
            'fal-ai/nano-banana' => 'Nano Banana',
            'fal-ai/flux/dev' => 'Flux Dev',
            'fal-ai/bytedance/seedream-4' => 'Seedream 4',
            'fal-ai/bytedance/seedream-5' => 'Seedream 5',
            'fal-ai/gpt-image-2' => 'GPT Image 2',
            'fal-ai/ideogram/v4' => 'Ideogram V4',
        ],
    ],

    'wavespeed' => [
        'base_url' => env('WAVESPEED_BASE_URL', 'https://api.wavespeed.ai'),
        'models' => [
            'wavespeed-ai/z-image/turbo' => 'Z-Image Turbo',
        ],
    ],
];
