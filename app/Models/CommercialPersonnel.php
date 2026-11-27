<?php

namespace App\Models;

use App\Policies\CommercialCatalogPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UsePolicy(CommercialCatalogPolicy::class)]
class CommercialPersonnel extends Model
{
    use SoftDeletes;

    protected $table = 'commercial_personnel';

    protected $fillable = [
        'name',
        'daily_cost_mxn',
        'daily_cost_usd',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'daily_cost_mxn' => 'decimal:2',
            'daily_cost_usd' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
