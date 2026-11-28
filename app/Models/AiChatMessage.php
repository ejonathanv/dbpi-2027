<?php

namespace App\Models;

use App\Enums\AiChatMessageRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    protected $fillable = [
        'ai_chat_conversation_id',
        'role',
        'content',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'role' => AiChatMessageRole::class,
            'meta' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiChatConversation::class, 'ai_chat_conversation_id');
    }

    public function hasPendingAction(): bool
    {
        return filled($this->meta['pending_action'] ?? null)
            && blank($this->meta['pending_action_resolved_at'] ?? null);
    }
}
