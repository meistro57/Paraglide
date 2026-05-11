<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AI\Backends\OllamaBackend;
use App\Services\AI\Backends\OpenRouterBackend;
use App\Services\AI\Contracts\AIBackend;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Log;

class BackendResolver
{
    public function __construct(
        private readonly OllamaBackend $ollamaBackend,
        private readonly OpenRouterBackend $openRouterBackend,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function forUser(?User $user): AIBackend
    {
        $preferred = $user?->preferred_backend ?? config('services.ai.default_backend', 'ollama');

        $backend = $preferred === 'openrouter'
            ? $this->openRouterBackend
            : $this->ollamaBackend;

        if ($backend->isAvailable()) {
            $this->auditLogger->log('ai_backend_selected', 'user', $user?->id, [
                'preferred' => $preferred,
                'resolved' => $preferred,
                'fallback' => false,
            ]);

            return $backend;
        }

        $fallback = $backend === $this->ollamaBackend
            ? $this->openRouterBackend
            : $this->ollamaBackend;

        if ($fallback->isAvailable()) {
            $resolved = $fallback === $this->openRouterBackend ? 'openrouter' : 'ollama';

            Log::warning('Preferred AI backend unavailable; falling back.', [
                'preferred' => $preferred,
                'resolved' => $resolved,
            ]);

            $this->auditLogger->log('ai_backend_fallback', 'user', $user?->id, [
                'preferred' => $preferred,
                'resolved' => $resolved,
                'fallback' => true,
            ]);

            return $fallback;
        }

        $this->auditLogger->log('ai_backend_unavailable', 'user', $user?->id, [
            'preferred' => $preferred,
            'resolved' => $preferred,
            'fallback' => false,
        ]);

        return $backend;
    }
}
