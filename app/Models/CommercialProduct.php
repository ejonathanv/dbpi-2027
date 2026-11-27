<?php

namespace App\Models;

use App\Policies\CommercialCatalogPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UsePolicy(CommercialCatalogPolicy::class)]
class CommercialProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'part_number',
        'sat_code',
        'commercial_product_category_id',
        'commercial_product_type_id',
        'brand',
        'model',
        'specifications',
        'image_paths',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'image_paths' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CommercialProductCategory::class, 'commercial_product_category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CommercialProductType::class, 'commercial_product_type_id');
    }
}
