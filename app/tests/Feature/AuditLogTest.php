<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Services\Audit\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_is_append_only_for_updates(): void
    {
        $log = AuditLog::query()->create([
            'action' => 'test_action',
            'resource_type' => 'test_resource',
            'resource_id' => 1,
            'metadata' => json_encode(['foo' => 'bar']),
            'created_at' => now(),
        ]);

        $this->expectException(LogicException::class);
        $log->update(['action' => 'mutated_action']);
    }

    public function test_audit_log_is_append_only_for_deletes(): void
    {
        $log = AuditLog::query()->create([
            'action' => 'test_action',
            'resource_type' => 'test_resource',
            'resource_id' => 1,
            'metadata' => json_encode(['foo' => 'bar']),
            'created_at' => now(),
        ]);

        $this->expectException(LogicException::class);
        $log->delete();
    }

    public function test_audit_logger_writes_encrypted_metadata(): void
    {
        $logger = app(AuditLogger::class);

        $logger->log('onboarding_step_advanced', 'onboarding_progress', 1, [
            'from' => 'welcome',
            'to' => 'password',
        ]);

        $stored = AuditLog::query()->first();

        $this->assertNotNull($stored);
        $this->assertSame('onboarding_step_advanced', $stored->action);
        $this->assertNotNull($stored->metadata);
        $this->assertStringContainsString('"from":"welcome"', $stored->metadata);

        $rawMetadata = DB::table('audit_logs')->value('metadata');

        $this->assertNotNull($rawMetadata);
        $this->assertNotSame($stored->metadata, $rawMetadata);
    }
}
