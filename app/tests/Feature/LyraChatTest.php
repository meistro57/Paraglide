<?php

namespace Tests\Feature;

use App\Livewire\LyraChat;
use App\Models\AiMessage;
use App\Models\AiThread;
use App\Services\AI\BackendResolver;
use App\Services\AI\Contracts\AIBackend;
use App\Services\AI\LyraChatService;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class LyraChatTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_send_message_persists_encrypted_messages_and_updates_thread(): void
    {
        $backend = new class implements AIBackend {
            public function streamChat(array $messages, array $options = []): Generator
            {
                yield 'unused';
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
        $this->app->instance(BackendResolver::class, $resolver);

        /** @var MockInterface&LyraChatService $chatService */
        $chatService = Mockery::mock(LyraChatService::class);
        $chatService->shouldReceive('streamResponse')->once()->andReturn((function (): Generator {
            yield 'Hi';
            yield ' there';
        })());
        $this->app->instance(LyraChatService::class, $chatService);

        $this->withSession([
            'paraglide.unlocked_at' => now()->timestamp,
        ]);

        Livewire::test(LyraChat::class)
            ->set('message', 'Hello Lyra')
            ->call('sendMessage')
            ->assertSet('message', '')
            ->assertSee('Hello Lyra')
            ->assertSee('Hi there');

        $thread = AiThread::query()->first();

        $this->assertNotNull($thread);
        $this->assertSame(2, $thread->message_count);
        $this->assertNotNull($thread->last_message_at);

        $messages = AiMessage::query()->orderBy('id')->get();

        $this->assertCount(2, $messages);
        $this->assertSame('user', $messages[0]->role);
        $this->assertSame('assistant', $messages[1]->role);
        $this->assertSame('Hello Lyra', $messages[0]->content_encrypted);
        $this->assertSame('Hi there', $messages[1]->content_encrypted);

        $rawUserMessage = DB::table('ai_messages')->where('id', $messages[0]->id)->value('content_encrypted');
        $rawAssistantMessage = DB::table('ai_messages')->where('id', $messages[1]->id)->value('content_encrypted');

        $this->assertNotSame('Hello Lyra', $rawUserMessage);
        $this->assertNotSame('Hi there', $rawAssistantMessage);

        $this->assertDatabaseHas('audit_logs', ['action' => 'chat_initiated', 'resource_type' => 'ai_thread']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'chat_message_sent', 'resource_type' => 'ai_thread']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_response_received', 'resource_type' => 'ai_thread']);
    }
}
