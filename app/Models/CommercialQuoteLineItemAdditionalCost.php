<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialQuoteLineItemAdditionalCost extends Model
{
    protected $fillable = [
        'commercial_quote_line_item_id',
        'concept',
        'amount',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function lineItem(): BelongsTo
    {
        return $this->belongsTo(CommercialQuoteLineItem::class, 'commercial_quote_line_item_id');
    }
}
