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
    | API services
    |--------------------------------------------------------------------------
    | The catalog of external services a team may connect. `type` is either
    | "media" (image generation) or "llm" (backstory analysis / prompt
    | enhancement). Per-team credentials are stored encrypted per service.
    */

    'fal' => [
        'label' => 'FAL.AI',
        'type' => 'media',
        'base_url' => env('FAL_BASE_URL', 'https://queue.fal.run'),
        'validate_url' => 'https://fal.run/users/me',
        'models' => [
            'fal-ai/bytedance/seedream/v4/text-to-image' => [
                'label' => 'Seedream 4',
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_16_9', '16:9' => 'landscape_16_9']],
                'defaults' => ['num_images' => 1, 'output_format' => 'png'],
            ],
            'bytedance/seedream/v5/pro/text-to-image' => [
                'label' => 'Seedream 5 Pro',
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_16_9', '16:9' => 'landscape_16_9']],
                'defaults' => ['num_images' => 1, 'output_format' => 'png'],
            ],
            'bytedance/seedream/v5/lite/text-to-image' => [
                'label' => 'Seedream 5 Lite',
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_16_9', '16:9' => 'landscape_16_9']],
                'defaults' => ['num_images' => 1],
            ],
            'fal-ai/gpt-image-1.5' => [
                'label' => 'GPT Image 1.5',
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => '1024x1536', '16:9' => '1536x1024']],
                'defaults' => ['num_images' => 1, 'quality' => 'medium'],
            ],
            'openai/gpt-image-2' => [
                'label' => 'GPT Image 2',
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_4_3', '16:9' => 'landscape_4_3']],
                'defaults' => ['num_images' => 1, 'quality' => 'medium'],
            ],
            'openai/gpt-image-2.5/flare/text-to-image' => [
                'label' => 'GPT Image 2.5 Flare',
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_4_3', '16:9' => 'landscape_4_3']],
                'defaults' => ['num_images' => 1, 'quality' => 'medium'],
            ],
            'openai/gpt-image-2.5/sunburst/text-to-image' => [
                'label' => 'GPT Image 2.5 Sunburst',
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_4_3', '16:9' => 'landscape_4_3']],
                'defaults' => ['num_images' => 1, 'quality' => 'medium'],
            ],
            'ideogram/v4' => [
                'label' => 'Ideogram V4',
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_16_9', '16:9' => 'landscape_16_9']],
                'defaults' => ['num_images' => 1],
            ],
            'fal-ai/nano-banana' => [
                'label' => 'Nano Banana',
                'size' => ['param' => 'aspect_ratio', 'sizes' => ['9:16' => '9:16', '16:9' => '16:9']],
                'defaults' => ['num_images' => 1, 'resolution' => '1K'],
            ],
            'fal-ai/nano-banana-2' => [
                'label' => 'Nano Banana 2',
                'size' => ['param' => 'aspect_ratio', 'sizes' => ['9:16' => '9:16', '16:9' => '16:9']],
                'defaults' => ['num_images' => 1, 'resolution' => '1K'],
            ],
            'fal-ai/flux/dev' => [
                'label' => 'Flux Dev',
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_16_9', '16:9' => 'landscape_16_9']],
                'defaults' => ['num_images' => 1],
            ],
            'fal-ai/flux/schnell' => [
                'label' => 'Flux Schnell',
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_16_9', '16:9' => 'landscape_16_9']],
                'defaults' => ['num_images' => 1],
            ],
        ],
    ],

    'wavespeed' => [
        'label' => 'WaveSpeed',
        'type' => 'media',
        'base_url' => env('WAVESPEED_BASE_URL', 'https://api.wavespeed.ai'),
        'validate_url' => 'https://api.wavespeed.ai/api/v3/balance',
        'models_url' => 'https://api.wavespeed.ai/api/v3/models',
        'model_families' => ['seedream', 'gpt-image', 'ideogram', 'nano-banana', 'flux'],
        'models' => [],
    ],

    'claude' => [
        'label' => 'Claude',
        'type' => 'llm',
        'lab' => 'anthropic',
        'config_key' => 'anthropic',
        'validate_url' => 'https://api.anthropic.com/v1/models',
        'model' => 'claude-haiku-4-5-20251001',
    ],

    'chatgpt' => [
        'label' => 'ChatGPT',
        'type' => 'llm',
        'lab' => 'openai',
        'config_key' => 'openai',
        'validate_url' => 'https://api.openai.com/v1/models',
        'model' => 'gpt-4o-mini',
    ],

    'deepseek' => [
        'label' => 'DeepSeek',
        'type' => 'llm',
        'lab' => 'deepseek',
        'config_key' => 'deepseek',
        'validate_url' => 'https://api.deepseek.com/user/balance',
        'model' => 'deepseek-chat',
    ],
];
