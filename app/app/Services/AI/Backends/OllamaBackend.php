<?php

namespace App\Services\AI\Backends;

use App\Services\AI\Contracts\AIBackend;
use Generator;
use Illuminate\Support\Facades\Http;

class OllamaBackend implements AIBackend
{
    /**
     * @var callable|null
     */
    private $transport;

    public function __construct(?callable $transport = null)
    {
        $this->transport = $transport;
    }

    public function streamChat(array $messages, array $options = []): Generator
    {
        $payload = array_merge([
            'model' => config('services.ollama.model'),
            'messages' => $messages,
            'stream' => true,
        ], $options);

        $lines = $this->send('/api/chat', $payload);

        foreach ($this->parseNdjson($lines) as $chunk) {
            yield $chunk;
        }
    }

    public function listModels(): array
    {
        if ($this->transport !== null) {
            $data = ($this->transport)('/api/tags', [], []);

            if (is_array($data) && array_key_exists('models', $data)) {
                return $data['models'];
            }

            return [];
        }

        $response = Http::baseUrl(config('services.ollama.base_url'))
            ->timeout(10)
            ->get('/api/tags')
            ->throw()
            ->json();

        return is_array($response['models'] ?? null) ? $response['models'] : [];
    }

    public function isAvailable(): bool
    {
        try {
            $this->listModels();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function send(string $endpoint, array $payload): iterable
    {
        if ($this->transport !== null) {
            return ($this->transport)($endpoint, $payload, []);
        }

        $body = Http::baseUrl(config('services.ollama.base_url'))
            ->timeout(120)
            ->post($endpoint, $payload)
            ->throw()
            ->body();

        return preg_split('/\r\n|\r|\n/', $body) ?: [];
    }

    private function parseNdjson(iterable $lines): Generator
    {
        foreach ($lines as $line) {
            if (! is_string($line) || trim($line) === '') {
                continue;
            }

            $decoded = json_decode(trim($line), true);

            if (! is_array($decoded)) {
                continue;
            }

            $chunk = $decoded['message']['content'] ?? null;

            if (is_string($chunk) && $chunk !== '') {
                yield $chunk;
            }
        }
    }
}
