<?php

namespace App\Models;

use App\Enums\CommercialQuoteLineItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialQuoteLineItem extends Model
{
    protected $fillable = [
        'commercial_quote_group_id',
        'type',
        'sort_order',
        'commercial_product_id',
        'product_alias',
        'show_part_number',
        'product_quantity',
        'show_product_image',
        'hide_product_in_quote',
        'product_exclude_from_margin',
        'commercial_supplier_id',
        'supplier_cost',
        'supplier_discount_percent',
        'supplier_special_discount_percent',
        'customer_discount_percent',
        'commercial_personnel_id',
        'personnel_quantity',
        'personnel_days',
        'helpdesk_contract_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => CommercialQuoteLineItemType::class,
            'sort_order' => 'integer',
            'show_part_number' => 'boolean',
            'product_quantity' => 'decimal:4',
            'show_product_image' => 'boolean',
            'hide_product_in_quote' => 'boolean',
            'product_exclude_from_margin' => 'boolean',
            'supplier_cost' => 'decimal:2',
            'supplier_discount_percent' => 'decimal:2',
            'supplier_special_discount_percent' => 'decimal:2',
            'customer_discount_percent' => 'decimal:2',
            'personnel_quantity' => 'decimal:4',
            'personnel_days' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CommercialQuoteGroup::class, 'commercial_quote_group_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CommercialProduct::class, 'commercial_product_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(CommercialSupplier::class, 'commercial_supplier_id');
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(CommercialPersonnel::class, 'commercial_personnel_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(HelpdeskContract::class, 'helpdesk_contract_id');
    }

    public function additionalCosts(): HasMany
    {
        return $this->hasMany(CommercialQuoteLineItemAdditionalCost::class, 'commercial_quote_line_item_id')
            ->orderBy('sort_order');
    }
}
