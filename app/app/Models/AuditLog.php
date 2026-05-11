<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class AuditLog extends Model
{
    use HasEncryptedAttributes;
    use HasFactory;

    public $timestamps = true;

    public const UPDATED_AT = null;

    protected $fillable = [
        'action',
        'resource_type',
        'resource_id',
        'metadata',
        'created_at',
    ];

    protected array $encrypted = [
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static function (): void {
            throw new LogicException('Audit logs are append-only.');
        });

        static::deleting(static function (): void {
            throw new LogicException('Audit logs are append-only.');
        });
    }
}
