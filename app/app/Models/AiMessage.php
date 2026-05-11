<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    use HasEncryptedAttributes;
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'role',
        'content_encrypted',
        'model_used',
        'tokens_in',
        'tokens_out',
    ];

    protected array $encrypted = [
        'content_encrypted',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(AiThread::class, 'thread_id');
    }
}
