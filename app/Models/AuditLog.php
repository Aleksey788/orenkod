<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    /** @param array<string, mixed>|null $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public static function recordAction(Model $subject, int $userId, string $event, ?array $oldValues = null, ?array $newValues = null): self
    {
        return self::create([
            'user_id' => $userId, 'active_role' => 'admin', 'event' => $event,
            'subject_type' => $subject->getMorphClass(), 'subject_id' => $subject->getKey(),
            'old_values' => $oldValues, 'new_values' => $newValues,
        ]);
    }

    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'active_role',
        'event',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
