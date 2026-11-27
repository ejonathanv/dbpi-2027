<?php

namespace App\Models;

use App\Enums\CommercialQuoteLineItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialQuoteGroup extends Model
{
    protected $fillable = [
        'commercial_quote_id',
        'title',
        'sort_order',
        'hide_title_in_quote',
        'hide_group_in_quote',
        'hide_items_in_quote',
        'exclude_from_margin',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'hide_title_in_quote' => 'boolean',
            'hide_group_in_quote' => 'boolean',
            'hide_items_in_quote' => 'boolean',
            'exclude_from_margin' => 'boolean',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CommercialQuote::class, 'commercial_quote_id');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(CommercialQuoteLineItem::class, 'commercial_quote_group_id')
            ->orderBy('sort_order');
    }

    public function productLineItems(): HasMany
    {
        return $this->lineItems()->where('type', CommercialQuoteLineItemType::Product);
    }

    public function personnelLineItems(): HasMany
    {
        return $this->lineItems()->where('type', CommercialQuoteLineItemType::Personnel);
    }

    public function contractLineItems(): HasMany
    {
        return $this->lineItems()->where('type', CommercialQuoteLineItemType::Contract);
    }
}
