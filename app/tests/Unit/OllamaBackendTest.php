<?php

namespace Tests\Unit;

use App\Services\AI\Backends\OllamaBackend;
use Tests\TestCase;

class OllamaBackendTest extends TestCase
{
    public function test_stream_chat_formats_request_and_parses_ndjson_chunks(): void
    {
        $captured = [];

        $backend = new OllamaBackend(function (string $endpoint, array $payload, array $headers) use (&$captured) {
            $captured = compact('endpoint', 'payload', 'headers');

            return [
                '{"message":{"content":"Hello"}}',
                '{"message":{"content":" world"}}',
            ];
        });

        $chunks = iterator_to_array($backend->streamChat([
            ['role' => 'user', 'content' => 'Hi'],
        ]));

        $this->assertSame(['Hello', ' world'], $chunks);
        $this->assertSame('/api/chat', $captured['endpoint']);
        $this->assertTrue($captured['payload']['stream']);
        $this->assertSame([], $captured['headers']);
    }

    public function test_list_models_uses_transport_result(): void
    {
        $backend = new OllamaBackend(function () {
            return ['models' => [
                ['name' => 'llama3.1:8b'],
                ['name' => 'qwen2.5:32b'],
            ]];
        });

        $models = $backend->listModels();

        $this->assertCount(2, $models);
        $this->assertSame('llama3.1:8b', $models[0]['name']);
    }
}
