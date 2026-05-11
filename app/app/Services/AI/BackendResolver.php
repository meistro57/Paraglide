<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AI\Backends\OllamaBackend;
use App\Services\AI\Backends\OpenRouterBackend;
use App\Services\AI\Contracts\AIBackend;
use Illuminate\Support\Facades\Log;

class BackendResolver
{
    public function __construct(
        private readonly OllamaBackend $ollamaBackend,
        private readonly OpenRouterBackend $openRouterBackend,
    ) {
    }

    public function forUser(?User $user): AIBackend
    {
        $preferred = $user?->preferred_backend ?? config('services.ai.default_backend', 'ollama');

        $backend = $preferred === 'openrouter'
            ? $this->openRouterBackend
            : $this->ollamaBackend;

        if ($backend->isAvailable()) {
            return $backend;
        }

        $fallback = $backend === $this->ollamaBackend
            ? $this->openRouterBackend
            : $this->ollamaBackend;

        if ($fallback->isAvailable()) {
            Log::warning('Preferred AI backend unavailable; falling back.', [
                'preferred' => $preferred,
            ]);

            return $fallback;
        }

        return $backend;
    }
}
