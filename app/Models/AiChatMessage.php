<?php

namespace App\Models;

use Database\Factories\AiChatMessageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    /** @use HasFactory<AiChatMessageFactory> */
    use HasFactory, HasUuids;

    public const ROLE_USER = 'user';

    public const ROLE_MODEL = 'model';

    public const SOURCE_USER = 'user';

    public const SOURCE_AI = 'ai';

    public const SOURCE_FALLBACK = 'fallback';

    public const SOURCE_BLOCKED_PII = 'blocked_pii';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_flagged' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'session_id');
    }
}
