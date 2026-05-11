<?php

return [
    'default_model' => env('OPENROUTER_DEFAULT_MODEL', 'anthropic/claude-sonnet-4'),

    'models' => [
        'anthropic/claude-opus-4',
        'anthropic/claude-sonnet-4',
        'anthropic/claude-haiku-4.5',
        'openai/gpt-4-turbo',
        'meta-llama/llama-3.3-70b-instruct',
        'qwen/qwen-2.5-72b-instruct',
    ],
];
