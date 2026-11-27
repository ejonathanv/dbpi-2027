<?php

namespace App\Models;

use App\Enums\HelpdeskAttentionType;
use App\Enums\HelpdeskEventVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpdeskTicketEvent extends Model
{
    protected $fillable = [
        'helpdesk_ticket_id',
        'description',
        'work_hours',
        'work_minutes',
        'attention_type',
        'travel_hours',
        'travel_minutes',
        'visibility',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'work_hours' => 'integer',
            'work_minutes' => 'integer',
            'travel_hours' => 'integer',
            'travel_minutes' => 'integer',
            'attention_type' => HelpdeskAttentionType::class,
            'visibility' => HelpdeskEventVisibility::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (HelpdeskTicketEvent $event): void {
            $event->created_by ??= auth()->id();
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTicket::class, 'helpdesk_ticket_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(HelpdeskTicketEventAttachment::class);
    }

    public function workTimeLabel(): string
    {
        return sprintf('%d h %02d min', $this->work_hours, $this->work_minutes);
    }
}
