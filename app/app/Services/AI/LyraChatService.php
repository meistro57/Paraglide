<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Generator;
use Throwable;

class LyraChatService
{
    public function __construct(
        private readonly BackendResolver $backendResolver,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function streamResponse(User $user, array $history, string $message, array $options = []): Generator
    {
        $backend = $this->backendResolver->forUser($user);
        $messages = $this->buildMessages($history, $message);

        $this->auditLogger->log('lyra_chat_requested', 'user', $user->id, [
            'history_messages' => count($history),
            'prompt_length' => mb_strlen($message),
            'options' => array_keys($options),
        ]);

        return $this->streamWithAudit($user, $backend->streamChat($messages, $options));
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

    private function streamWithAudit(User $user, Generator $stream): Generator
    {
        $chunks = 0;

        try {
            foreach ($stream as $chunk) {
                $chunks++;
                yield $chunk;
            }

            $this->auditLogger->log('lyra_chat_stream_completed', 'user', $user->id, [
                'chunks' => $chunks,
            ]);
        } catch (Throwable $exception) {
            $this->auditLogger->log('lyra_chat_stream_failed', 'user', $user->id, [
                'chunks' => $chunks,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
