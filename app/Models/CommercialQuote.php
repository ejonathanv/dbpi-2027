<?php

namespace App\Models;

use App\Enums\CommercialQuoteAuthorizationStatus;
use App\Enums\SalaryCurrency;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CommercialQuote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quote_number',
        'crm_opportunity_id',
        'crm_account_id',
        'crm_contact_id',
        'payment_terms',
        'delivery_time',
        'project_description',
        'technical_summary',
        'key_benefits',
        'observations',
        'quoted_at',
        'presentation_currency',
        'exchange_rate',
        'validity_days',
        'fob',
        'general_discount_percent',
        'vat_percent',
        'vat_included',
        'created_by_user_id',
        'authorization_status',
        'frozen_snapshot',
        'authorization_locked_at',
    ];

    protected function casts(): array
    {
        return [
            'quoted_at' => 'date',
            'presentation_currency' => SalaryCurrency::class,
            'exchange_rate' => 'decimal:6',
            'validity_days' => 'integer',
            'general_discount_percent' => 'decimal:2',
            'vat_percent' => 'decimal:2',
            'vat_included' => 'boolean',
            'authorization_status' => CommercialQuoteAuthorizationStatus::class,
            'frozen_snapshot' => 'array',
            'authorization_locked_at' => 'datetime',
        ];
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

        if (! in_array($this->authorization_status, [
            CommercialQuoteAuthorizationStatus::Draft,
            CommercialQuoteAuthorizationStatus::ChangesRequested,
            CommercialQuoteAuthorizationStatus::Rejected,
        ], true)) {
            return false;
        }

        return $this->lineItems()->exists();
    }

    public function authorizationRequests(): HasMany
    {
        return $this->hasMany(CommercialQuoteAuthorizationRequest::class)
            ->orderByDesc('requested_at');
    }

    protected function generalDiscountPercent(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): float => max(0, (float) (filled($value) ? $value : 0)),
        );
    }

    public static function generateQuoteNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "COM-{$year}-";
        $latest = static::query()
            ->where('quote_number', 'like', "{$prefix}%")
            ->orderByDesc('quote_number')
            ->value('quote_number');

        if (blank($latest)) {
            return $prefix.'0001';
        }

        $sequence = (int) Str::afterLast($latest, '-');
        $next = str_pad((string) ($sequence + 1), 4, '0', STR_PAD_LEFT);

        return $prefix.$next;
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(CrmOpportunity::class, 'crm_opportunity_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CrmAccount::class, 'crm_account_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'crm_contact_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(CommercialQuoteGroup::class, 'commercial_quote_id')
            ->orderBy('sort_order');
    }

    public function lineItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            CommercialQuoteLineItem::class,
            CommercialQuoteGroup::class,
            'commercial_quote_id',
            'commercial_quote_group_id',
            'id',
            'id',
        );
    }
}
