<?php

namespace App\Models;

use App\Enums\AiChatConversationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiChatConversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'status',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'status' => AiChatConversationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class)->orderBy('id');
    }
}
