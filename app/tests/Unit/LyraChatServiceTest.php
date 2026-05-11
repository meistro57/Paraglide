<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AI\BackendResolver;
use App\Services\AI\Contracts\AIBackend;
use App\Services\AI\LyraChatService;
use App\Services\Audit\AuditLogger;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class LyraChatServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_build_messages_includes_system_prompt_history_and_user_input(): void
    {
        config()->set('lyra.system_prompt', 'System prompt');

        $resolver = Mockery::mock(BackendResolver::class);
        $auditLogger = Mockery::mock(AuditLogger::class);
        $service = new LyraChatService($resolver, $auditLogger);

        $messages = $service->buildMessages([
            ['role' => 'assistant', 'content' => 'Hi there'],
            ['role' => 'user', 'content' => 'Need case help'],
        ], 'New question');

        $this->assertSame('system', $messages[0]['role']);
        $this->assertSame('System prompt', $messages[0]['content']);
        $this->assertSame('assistant', $messages[1]['role']);
        $this->assertSame('user', $messages[2]['role']);
        $this->assertSame('user', $messages[3]['role']);
        $this->assertSame('New question', $messages[3]['content']);
    }

    public function test_stream_response_uses_resolved_backend_and_yields_chunks(): void
    {
        config()->set('lyra.system_prompt', 'System prompt');

        $backend = new class implements AIBackend {
            public array $capturedMessages = [];

            public function streamChat(array $messages, array $options = []): \Generator
            {
                $this->capturedMessages = $messages;

                yield 'Chunk 1';
                yield 'Chunk 2';
            }

            public function listModels(): array
            {
                return [];
            }

            public function isAvailable(): bool
            {
                return true;
            }
        };

        /** @var MockInterface&BackendResolver $resolver */
        $resolver = Mockery::mock(BackendResolver::class);
        $resolver->shouldReceive('forUser')->once()->andReturn($backend);

        /** @var MockInterface&AuditLogger $auditLogger */
        $auditLogger = Mockery::mock(AuditLogger::class);
        $auditLogger->shouldReceive('log')->once()->with(
            'lyra_chat_requested',
            'user',
            null,
            [
                'history_messages' => 0,
                'prompt_length' => 10,
                'options' => [],
            ],
        )->andReturn(new AuditLog());
        $auditLogger->shouldReceive('log')->once()->with(
            'lyra_chat_stream_completed',
            'user',
            null,
            [
                'chunks' => 2,
            ],
        )->andReturn(new AuditLog());

        $service = new LyraChatService($resolver, $auditLogger);
        $user = new User(['preferred_backend' => 'ollama']);

        $chunks = iterator_to_array($service->streamResponse($user, [], 'Hello Lyra'));

        $this->assertSame(['Chunk 1', 'Chunk 2'], $chunks);
        $this->assertSame('system', $backend->capturedMessages[0]['role']);
        $this->assertSame('user', $backend->capturedMessages[1]['role']);
        $this->assertSame('Hello Lyra', $backend->capturedMessages[1]['content']);
    }
}
