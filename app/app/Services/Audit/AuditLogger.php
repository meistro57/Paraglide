<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use JsonException;

class AuditLogger
{
    public function log(string $action, string $resourceType, ?int $resourceId = null, array $metadata = []): AuditLog
    {
        return AuditLog::query()->create([
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'metadata' => $metadata === [] ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
