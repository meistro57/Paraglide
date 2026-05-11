<?php

namespace App\Services\AI\Backends;

use App\Services\AI\Contracts\AIBackend;
use Generator;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenRouterBackend implements AIBackend
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
            'model' => $options['model'] ?? config('openrouter.default_model'),
            'messages' => $messages,
            'stream' => true,
        ], $options);

        unset($payload['api_key']);

        $headers = $this->headers($options['api_key'] ?? null);
        $lines = $this->send('/chat/completions', $payload, $headers);

        foreach ($this->parseSse($lines) as $chunk) {
            yield $chunk;
        }
    }

    public function listModels(): array
    {
        return config('openrouter.models', []);
    }

    public function isAvailable(): bool
    {
        try {
            $this->headers();

            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    private function send(string $endpoint, array $payload, array $headers): iterable
    {
        if ($this->transport !== null) {
            return ($this->transport)($endpoint, $payload, $headers);
        }

        $body = Http::baseUrl(config('services.openrouter.base_url'))
            ->withHeaders($headers)
            ->timeout(120)
            ->post($endpoint, $payload)
            ->throw()
            ->body();

        return preg_split('/\r\n|\r|\n/', $body) ?: [];
    }

    private function headers(?string $overrideApiKey = null): array
    {
        $apiKey = $overrideApiKey ?? config('services.openrouter.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('OpenRouter API key is missing.');
        }

        return [
            'Authorization' => 'Bearer '.$apiKey,
            'HTTP-Referer' => config('services.openrouter.referer'),
            'X-Title' => config('services.openrouter.title'),
        ];
    }

    private function parseSse(iterable $lines): Generator
    {
        foreach ($lines as $line) {
            if (! is_string($line)) {
                continue;
            }

            $line = trim($line);

            if (! str_starts_with($line, 'data:')) {
                continue;
            }

            $payload = trim(substr($line, 5));

            if ($payload === '' || $payload === '[DONE]') {
                continue;
            }

            $decoded = json_decode($payload, true);

            if (! is_array($decoded)) {
                continue;
            }

            $chunk = $decoded['choices'][0]['delta']['content'] ?? null;

            if (is_string($chunk) && $chunk !== '') {
                yield $chunk;
            }
        }
    }
}
