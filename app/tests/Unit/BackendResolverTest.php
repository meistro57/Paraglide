<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AI\BackendResolver;
use App\Services\AI\Backends\OllamaBackend;
use App\Services\AI\Backends\OpenRouterBackend;
use Tests\TestCase;

class BackendResolverTest extends TestCase
{
    public function test_resolver_falls_back_when_preferred_backend_unavailable(): void
    {
        config()->set('services.openrouter.api_key', null);

        $ollama = new OllamaBackend(function () {
            return ['models' => []];
        });

        $openRouter = new OpenRouterBackend();

        $resolver = new BackendResolver($ollama, $openRouter);

        $user = new User([
            'preferred_backend' => 'openrouter',
        ]);

        $resolved = $resolver->forUser($user);

        $this->assertInstanceOf(OllamaBackend::class, $resolved);
    }

    public function test_resolver_returns_preferred_backend_when_available(): void
    {
        config()->set('services.openrouter.api_key', 'test-key');

        $ollama = new OllamaBackend(function () {
            return ['models' => []];
        });

        $openRouter = new OpenRouterBackend();

        $resolver = new BackendResolver($ollama, $openRouter);

        $user = new User([
            'preferred_backend' => 'openrouter',
        ]);

        $resolved = $resolver->forUser($user);

        $this->assertInstanceOf(OpenRouterBackend::class, $resolved);
    }
}
