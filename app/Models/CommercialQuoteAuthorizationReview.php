<?php

namespace App\Models;

use App\Enums\CommercialQuoteAuthorizationReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialQuoteAuthorizationReview extends Model
{
    protected $fillable = [
        'commercial_quote_authorization_request_id',
        'user_id',
        'status',
        'notes',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommercialQuoteAuthorizationReviewStatus::class,
            'responded_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(CommercialQuoteAuthorizationRequest::class, 'commercial_quote_authorization_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
