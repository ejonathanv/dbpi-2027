<?php

namespace App\Models;

use App\Policies\CommercialCatalogPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UsePolicy(CommercialCatalogPolicy::class)]
class CommercialFup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'commercial_quote_id',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CommercialQuote::class, 'commercial_quote_id');
    }
}
