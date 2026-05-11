<?php

namespace App\Services\AI\Contracts;

use Generator;

interface AIBackend
{
    public function streamChat(array $messages, array $options = []): Generator;

    public function listModels(): array;

    public function isAvailable(): bool;
}
