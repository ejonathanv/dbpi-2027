<?php

namespace App\Models;

use App\Enums\CrmOpportunitySource;
use App\Enums\CrmOpportunityStage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmOpportunity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'crm_account_id',
        'crm_contact_id',
        'assigned_user_id',
        'name',
        'description',
        'stage',
        'amount',
        'currency',
        'probability_percent',
        'source',
        'fob',
        'use_account_vat',
        'vat_treatment',
        'estimated_delivery_time',
        'use_account_delivery_days',
        'delivery_days',
        'delivery_schedule',
        'allows_partial_deliveries',
        'use_account_payment_terms',
        'advance_percent',
        'advance_days',
        'on_delivery_percent',
        'on_delivery_days',
        'installation_percent',
        'installation_days',
        'expected_close_date',
    ];

    protected function casts(): array
    {
        return [
            'stage' => CrmOpportunityStage::class,
            'source' => CrmOpportunitySource::class,
            'amount' => 'decimal:2',
            'probability_percent' => 'decimal:2',
            'advance_percent' => 'decimal:2',
            'on_delivery_percent' => 'decimal:2',
            'installation_percent' => 'decimal:2',
            'use_account_vat' => 'boolean',
            'use_account_delivery_days' => 'boolean',
            'allows_partial_deliveries' => 'boolean',
            'use_account_payment_terms' => 'boolean',
            'expected_close_date' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CrmAccount::class, 'crm_account_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'crm_contact_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CrmCalendarEvent::class);
    }

    public function resolvedVatTreatment(): ?string
    {
        if ($this->use_account_vat) {
            return $this->account?->vat_treatment;
        }

        return $this->vat_treatment;
    }

    public function resolvedDeliveryDays(): ?string
    {
        if ($this->use_account_delivery_days) {
            return $this->account?->delivery_days;
        }

        return $this->delivery_days;
    }

    public function resolvedDeliverySchedule(): ?string
    {
        if ($this->use_account_delivery_days) {
            return $this->account?->delivery_schedule;
        }

        return $this->delivery_schedule;
    }

    public function resolvedAllowsPartialDeliveries(): bool
    {
        if ($this->use_account_delivery_days) {
            return (bool) $this->account?->allows_partial_deliveries;
        }

        return (bool) $this->allows_partial_deliveries;
    }
}
