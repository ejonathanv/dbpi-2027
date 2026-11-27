<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialFupContractItem extends Model
{
    protected $fillable = [
        'commercial_fup_id',
        'commercial_quote_line_item_id',
        'helpdesk_contract_id',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function fup(): BelongsTo
    {
        return $this->belongsTo(CommercialFup::class, 'commercial_fup_id');
    }

    public function lineItem(): BelongsTo
    {
        return $this->belongsTo(CommercialQuoteLineItem::class, 'commercial_quote_line_item_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(HelpdeskContract::class, 'helpdesk_contract_id');
    }
}