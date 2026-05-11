<?php

namespace Tests\Feature;

use App\Models\AiMessage;
use App\Models\AiThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EncryptedAttributesTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_thread_title_is_encrypted_at_rest_and_decrypted_on_read(): void
    {
        $user = User::factory()->create();

        $thread = AiThread::query()->create([
            'user_id' => $user->id,
            'title' => 'Case strategy notes',
            'message_count' => 0,
        ]);

        $stored = DB::table('ai_threads')->where('id', $thread->id)->value('title');

        $this->assertNotSame('Case strategy notes', $stored);
        $this->assertSame('Case strategy notes', $thread->fresh()->title);
    }

    public function test_ai_message_content_round_trips_through_encrypted_attribute_trait(): void
    {
        $user = User::factory()->create();

        $thread = AiThread::query()->create([
            'user_id' => $user->id,
            'title' => 'Thread title',
            'message_count' => 0,
        ]);

        $message = AiMessage::query()->create([
            'thread_id' => $thread->id,
            'role' => 'user',
            'content_encrypted' => 'Privileged message content',
        ]);

        $stored = DB::table('ai_messages')->where('id', $message->id)->value('content_encrypted');

        $this->assertNotSame('Privileged message content', $stored);
        $this->assertSame('Privileged message content', $message->fresh()->content_encrypted);
    }
}
