<?php

namespace App\Services\AI;

use App\Models\User;
use Generator;

class LyraChatService
{
    public function __construct(private readonly BackendResolver $backendResolver)
    {
    }

    public function streamResponse(User $user, array $history, string $message, array $options = []): Generator
    {
        $backend = $this->backendResolver->forUser($user);
        $messages = $this->buildMessages($history, $message);

        return $backend->streamChat($messages, $options);
    }

    public function buildMessages(array $history, string $message): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => config('lyra.system_prompt'),
            ],
        ];

        foreach ($history as $item) {
            if (! is_array($item)) {
                continue;
            }

            $role = $item['role'] ?? null;
            $content = $item['content'] ?? null;

            if (! is_string($role) || ! is_string($content)) {
                continue;
            }

            $messages[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        return $messages;
    }
}
