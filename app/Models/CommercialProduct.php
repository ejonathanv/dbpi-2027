<?php

namespace App\Models;

use App\Policies\CommercialCatalogPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UsePolicy(CommercialCatalogPolicy::class)]
class CommercialProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
