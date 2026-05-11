<?php

namespace Tests\Unit;

use App\Services\AI\Backends\OpenRouterBackend;
use Tests\TestCase;

class OpenRouterBackendTest extends TestCase
{
    public function test_stream_chat_formats_headers_and_parses_sse_chunks(): void
    {
        config()->set('services.openrouter.referer', 'https://paraglide.test');
        config()->set('services.openrouter.title', 'Paraglide Test');

        $captured = [];

        $backend = new OpenRouterBackend(function (string $endpoint, array $payload, array $headers) use (&$captured) {
            $captured = compact('endpoint', 'payload', 'headers');

            return [
                'data: {"choices":[{"delta":{"content":"Hello"}}]}',
                'data: {"choices":[{"delta":{"content":" from OpenRouter"}}]}',
                'data: [DONE]',
            ];
        });

        $chunks = iterator_to_array($backend->streamChat(
            [['role' => 'user', 'content' => 'Hi']],
            ['api_key' => 'test-key', 'model' => 'anthropic/claude-sonnet-4']
        ));

        $this->assertSame(['Hello', ' from OpenRouter'], $chunks);
        $this->assertSame('/chat/completions', $captured['endpoint']);
        $this->assertTrue($captured['payload']['stream']);
        $this->assertSame('Bearer test-key', $captured['headers']['Authorization']);
        $this->assertSame('https://paraglide.test', $captured['headers']['HTTP-Referer']);
        $this->assertSame('Paraglide Test', $captured['headers']['X-Title']);
    }

    public function test_is_available_returns_false_without_api_key(): void
    {
        config()->set('services.openrouter.api_key', null);

        $backend = new OpenRouterBackend();

        $this->assertFalse($backend->isAvailable());
    }
}
