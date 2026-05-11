<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AI\BackendResolver;
use App\Services\AI\Backends\OllamaBackend;
use App\Services\AI\Backends\OpenRouterBackend;
use App\Services\Audit\AuditLogger;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class BackendResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_resolver_falls_back_when_preferred_backend_unavailable(): void
    {
        config()->set('services.openrouter.api_key', null);

        $ollama = new OllamaBackend(function () {
            return ['models' => []];
        });

        $openRouter = new OpenRouterBackend();

        /** @var MockInterface&AuditLogger $auditLogger */
        $auditLogger = Mockery::mock(AuditLogger::class);
        $auditLogger->shouldReceive('log')->once()->with(
            'ai_backend_fallback',
            'user',
            null,
            [
                'preferred' => 'openrouter',
                'resolved' => 'ollama',
                'fallback' => true,
            ],
        )->andReturn(new \App\Models\AuditLog());

        $resolver = new BackendResolver($ollama, $openRouter, $auditLogger);

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

        /** @var MockInterface&AuditLogger $auditLogger */
        $auditLogger = Mockery::mock(AuditLogger::class);
        $auditLogger->shouldReceive('log')->once()->with(
            'ai_backend_selected',
            'user',
            null,
            [
                'preferred' => 'openrouter',
                'resolved' => 'openrouter',
                'fallback' => false,
            ],
        )->andReturn(new \App\Models\AuditLog());

        $resolver = new BackendResolver($ollama, $openRouter, $auditLogger);

        $user = new User([
            'preferred_backend' => 'openrouter',
        ]);

        $resolved = $resolver->forUser($user);

        $this->assertInstanceOf(OpenRouterBackend::class, $resolved);
    }
}
