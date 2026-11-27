<?php

namespace App\Models;

use App\Policies\CommercialCatalogPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UsePolicy(CommercialCatalogPolicy::class)]
class CommercialProductCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(CommercialProduct::class, 'commercial_product_category_id');
    }
}
