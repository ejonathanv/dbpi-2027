<?php

namespace App\Models;

use App\Enums\CommercialQuoteAuthorizationStatus;
use App\Policies\CommercialCatalogPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[UsePolicy(CommercialCatalogPolicy::class)]
class CommercialFup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fup_number',
        'commercial_quote_id',
        'client_po_number',
        'fup_date',
        'observations_and_recommendations',
        'commitment_delivery_date',
        'commitment_installation_start_date',
        'commitment_project_date',
        'account_executive_user_id',
        'authorization_status',
        'frozen_snapshot',
        'authorization_locked_at',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'fup_date' => 'date',
            'commitment_delivery_date' => 'date',
            'commitment_installation_start_date' => 'date',
            'commitment_project_date' => 'date',
            'authorization_status' => CommercialQuoteAuthorizationStatus::class,
            'frozen_snapshot' => 'array',
            'authorization_locked_at' => 'datetime',
        ];
    }

    public static function generateFupNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "FUP-{$year}-";

        $maxSequence = static::withTrashed()
            ->where('fup_number', 'like', "{$prefix}%")
            ->pluck('fup_number')
            ->map(fn (string $number): int => (int) Str::afterLast($number, '-'))
            ->max();

        $next = ($maxSequence ?? 0) + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CommercialQuote::class, 'commercial_quote_id');
    }

    public function accountExecutive(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_executive_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function authorizationRequests(): HasMany
    {
        return $this->hasMany(CommercialFupAuthorizationRequest::class)->orderByDesc('requested_at');
    }

    public function contractItems(): HasMany
    {
        return $this->hasMany(CommercialFupContractItem::class);
    }

    public function latestAuthorizationRequest(): HasOne
    {
        return $this->hasOne(CommercialFupAuthorizationRequest::class)->latestOfMany('requested_at');
    }

    public function isEditingLocked(): bool
    {
        return in_array($this->authorization_status, [
            CommercialQuoteAuthorizationStatus::PendingAuthorization,
            CommercialQuoteAuthorizationStatus::Authorized,
        ], true);
    }

    public function canRequestAuthorization(): bool
    {
        if ($this->isEditingLocked()) {
            return false;
        }

        return in_array($this->authorization_status, [
            CommercialQuoteAuthorizationStatus::Draft,
            CommercialQuoteAuthorizationStatus::ChangesRequested,
            CommercialQuoteAuthorizationStatus::Rejected,
        ], true);
    }
}