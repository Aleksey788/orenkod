<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalDataConsent extends Model
{
    protected $table = 'personal_data_consents';

    protected $fillable = [
        'user_id',
        'consent_type',
        'consent_version',
        'agreed_at',
        'revoked_at',
        'guardian_full_name',
        'guardian_relationship',
        'document_path',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'agreed_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}