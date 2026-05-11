<?php

namespace App\Livewire;

use App\Models\AiMessage;
use App\Models\AiThread;
use App\Models\User;
use App\Services\AI\BackendResolver;
use App\Services\AI\Contracts\AIBackend;
use App\Services\AI\LyraChatService;
use App\Services\Audit\AuditLogger;
use App\Services\Security\SessionUnlockManager;
use Livewire\Component;

class LyraChat extends Component
{
    public string $message = '';

    public array $messages = [];

    public ?int $threadId = null;

    public string $backendBadge = 'Unavailable';

    public function mount(BackendResolver $resolver, AuditLogger $auditLogger): void
    {
        $user = $this->resolveUser();
        $thread = AiThread::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'title' => 'Conversation with Lyra',
                'last_message_at' => now(),
                'message_count' => 0,
            ],
        );

        $this->threadId = $thread->id;
        $this->messages = $this->serializeMessages($thread);

        $backend = $resolver->forUser($user);
        $this->backendBadge = $this->backendLabel($backend, $user);

        if ($thread->wasRecentlyCreated) {
            $auditLogger->log('chat_initiated', 'ai_thread', $thread->id, [
                'backend' => $this->backendBadge,
            ]);
        }
    }

    public function sendMessage(
        LyraChatService $chatService,
        AuditLogger $auditLogger,
        SessionUnlockManager $unlockManager,
    ): void {
        if (! $unlockManager->isUnlocked()) {
            $this->redirectRoute('lock.show', navigate: true);

            return;
        }

        $unlockManager->touch();

        $this->validate([
            'message' => ['required', 'string'],
        ]);

        $prompt = trim($this->message);

        if ($prompt === '') {
            return;
        }

        $user = $this->resolveUser();
        $thread = $this->resolveThread($user);

        $history = $thread->messages()
            ->oldest('id')
            ->get()
            ->map(fn (AiMessage $message): array => [
                'role' => $message->role,
                'content' => $message->content_encrypted,
            ])
            ->all();

        $thread->messages()->create([
            'role' => 'user',
            'content_encrypted' => $prompt,
            'tokens_in' => null,
            'tokens_out' => null,
        ]);

        $auditLogger->log('chat_message_sent', 'ai_thread', $thread->id, [
            'backend' => $this->backendBadge,
            'prompt_length' => mb_strlen($prompt),
        ]);

        $this->messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        $this->message = '';

        $assistantResponse = '';

        foreach ($chatService->streamResponse($user, $history, $prompt, ['model' => $user->preferred_model]) as $chunk) {
            if (! is_string($chunk) || $chunk === '') {
                continue;
            }

            $assistantResponse .= $chunk;
            $this->stream(to: 'assistant_stream', content: $assistantResponse, replace: true);
        }

        if ($assistantResponse === '') {
            return;
        }

        $thread->messages()->create([
            'role' => 'assistant',
            'content_encrypted' => $assistantResponse,
            'model_used' => $user->preferred_model,
            'tokens_in' => null,
            'tokens_out' => null,
        ]);

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $assistantResponse,
        ];

        $thread->forceFill([
            'last_message_at' => now(),
            'message_count' => $thread->messages()->count(),
        ])->save();

        $auditLogger->log('ai_response_received', 'ai_thread', $thread->id, [
            'backend' => $this->backendBadge,
            'response_length' => mb_strlen($assistantResponse),
        ]);
    }

    public function render()
    {
        return view('livewire.lyra-chat');
    }

    private function resolveUser(): User
    {
        return User::query()->firstOrCreate(
            ['id' => 1],
            [
                'display_name' => 'Operator',
                'encryption_key_wrapped' => random_bytes(64),
                'recovery_code_hash' => hash('sha256', 'placeholder-recovery-code'),
                'hardware_tier' => 'tier_1',
                'preferred_backend' => 'ollama',
                'preferred_model' => config('services.ollama.model'),
                'totp_secret_encrypted' => null,
            ],
        );
    }

    private function resolveThread(User $user): AiThread
    {
        if ($this->threadId !== null) {
            $existing = AiThread::query()->find($this->threadId);

            if ($existing !== null) {
                return $existing;
            }
        }

        $thread = AiThread::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'title' => 'Conversation with Lyra',
                'last_message_at' => now(),
                'message_count' => 0,
            ],
        );

        $this->threadId = $thread->id;

        return $thread;
    }

    private function serializeMessages(AiThread $thread): array
    {
        return $thread->messages()
            ->oldest('id')
            ->get()
            ->map(fn (AiMessage $message): array => [
                'role' => $message->role,
                'content' => $message->content_encrypted,
            ])
            ->all();
    }

    private function backendLabel(AIBackend $backend, User $user): string
    {
        $name = class_basename($backend);

        if ($name === 'OllamaBackend') {
            return 'Ollama: '.$user->preferred_model;
        }

        if ($name === 'OpenRouterBackend') {
            return 'OpenRouter: '.$user->preferred_model;
        }

        return 'AI Backend';
    }
}
