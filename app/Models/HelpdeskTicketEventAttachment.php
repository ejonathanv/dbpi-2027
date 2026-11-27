<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpdeskTicketEventAttachment extends Model
{
    protected $fillable = [
        'helpdesk_ticket_event_id',
        'path',
        'original_name',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTicketEvent::class, 'helpdesk_ticket_event_id');
    }
}
