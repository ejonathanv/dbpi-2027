<?php

namespace App\Models;

use App\Enums\CommercialQuoteAuthorizationRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialFupAuthorizationRequest extends Model
{
    protected $fillable = [
        'commercial_fup_id',
        'requested_by_user_id',
        'status',
        'frozen_snapshot',
        'requested_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommercialQuoteAuthorizationRequestStatus::class,
            'frozen_snapshot' => 'array',
            'requested_at' => 'datetime',
        ];
    }

    public function fup(): BelongsTo
    {
        return $this->belongsTo(CommercialFup::class, 'commercial_fup_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CommercialFupAuthorizationReview::class);
    }
}