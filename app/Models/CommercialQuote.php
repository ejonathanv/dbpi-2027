<?php

namespace App\Models;

use App\Enums\CommercialQuoteAuthorizationStatus;
use App\Enums\CommercialQuoteWorkflowStep;
use App\Enums\SalaryCurrency;
use Illuminate\Database\Eloquent\Builder;
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
        'workflow_step',
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
            'workflow_step' => CommercialQuoteWorkflowStep::class,
        ];
    }

    public function activeWorkflowStep(): CommercialQuoteWorkflowStep
    {
        $step = $this->workflow_step ?? CommercialQuoteWorkflowStep::InProgress;

        if ($step === CommercialQuoteWorkflowStep::Cancelled) {
            return CommercialQuoteWorkflowStep::Cancelled;
        }

        if ($step === CommercialQuoteWorkflowStep::ClientAuthorized) {
            return CommercialQuoteWorkflowStep::ClientAuthorized;
        }

        return match ($this->authorization_status) {
            CommercialQuoteAuthorizationStatus::PendingAuthorization => CommercialQuoteWorkflowStep::WaitingInternalAuthorization,
            CommercialQuoteAuthorizationStatus::ChangesRequested => CommercialQuoteWorkflowStep::NegotiationReview,
            CommercialQuoteAuthorizationStatus::Rejected => CommercialQuoteWorkflowStep::InProgress,
            CommercialQuoteAuthorizationStatus::Authorized => in_array($step, [
                CommercialQuoteWorkflowStep::AwaitingClientAuthorization,
                CommercialQuoteWorkflowStep::NegotiationReview,
            ], true)
                ? $step
                : CommercialQuoteWorkflowStep::InternallyAuthorized,
            default => $step,
        };
    }

    /**
     * @return 'completed'|'current'|'upcoming'
     */
    public function workflowStepVisualState(CommercialQuoteWorkflowStep $step): string
    {
        $active = $this->activeWorkflowStep();

        if ($active === CommercialQuoteWorkflowStep::Cancelled) {
            if ($step === CommercialQuoteWorkflowStep::Cancelled) {
                return 'current';
            }

            return $step->order() < $active->order() ? 'completed' : 'upcoming';
        }

        if ($active === CommercialQuoteWorkflowStep::ClientAuthorized) {
            if ($step === CommercialQuoteWorkflowStep::ClientAuthorized) {
                return 'current';
            }

            return $step->order() < $active->order() ? 'completed' : 'upcoming';
        }

        $activeOrder = $active->order();
        $stepOrder = $step->order();

        if ($stepOrder < $activeOrder) {
            return 'completed';
        }

        if ($step === $active) {
            return 'current';
        }

        return 'upcoming';
    }

    public function scopeWhereActiveWorkflowStep(Builder $query, CommercialQuoteWorkflowStep $step): Builder
    {
        return match ($step) {
            CommercialQuoteWorkflowStep::Cancelled => $query->where(
                'workflow_step',
                CommercialQuoteWorkflowStep::Cancelled,
            ),
            CommercialQuoteWorkflowStep::ClientAuthorized => $query->where(
                'workflow_step',
                CommercialQuoteWorkflowStep::ClientAuthorized,
            ),
            CommercialQuoteWorkflowStep::WaitingInternalAuthorization => $query->where(
                'authorization_status',
                CommercialQuoteAuthorizationStatus::PendingAuthorization,
            ),
            CommercialQuoteWorkflowStep::NegotiationReview => $query->where(
                'authorization_status',
                CommercialQuoteAuthorizationStatus::ChangesRequested,
            ),
            CommercialQuoteWorkflowStep::InternallyAuthorized => $query
                ->where('authorization_status', CommercialQuoteAuthorizationStatus::Authorized)
                ->where('workflow_step', CommercialQuoteWorkflowStep::InternallyAuthorized),
            CommercialQuoteWorkflowStep::AwaitingClientAuthorization => $query
                ->where('authorization_status', CommercialQuoteAuthorizationStatus::Authorized)
                ->where('workflow_step', CommercialQuoteWorkflowStep::AwaitingClientAuthorization),
            CommercialQuoteWorkflowStep::InProgress => $query
                ->whereNotIn('workflow_step', [
                    CommercialQuoteWorkflowStep::Cancelled->value,
                    CommercialQuoteWorkflowStep::ClientAuthorized->value,
                ])
                ->whereNotIn('authorization_status', [
                    CommercialQuoteAuthorizationStatus::PendingAuthorization->value,
                    CommercialQuoteAuthorizationStatus::Authorized->value,
                    CommercialQuoteAuthorizationStatus::ChangesRequested->value,
                ]),
        };
    }

    public function isCancelled(): bool
    {
        return $this->workflow_step === CommercialQuoteWorkflowStep::Cancelled
            || $this->activeWorkflowStep() === CommercialQuoteWorkflowStep::Cancelled;
    }

    public function isEditingLocked(): bool
    {
        if ($this->isCancelled()) {
            return true;
        }

        return in_array($this->authorization_status, [
            CommercialQuoteAuthorizationStatus::PendingAuthorization,
            CommercialQuoteAuthorizationStatus::Authorized,
        ], true);
    }

    public function canCancelQuote(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        return in_array($this->activeWorkflowStep(), [
            CommercialQuoteWorkflowStep::InternallyAuthorized,
            CommercialQuoteWorkflowStep::AwaitingClientAuthorization,
            CommercialQuoteWorkflowStep::NegotiationReview,
        ], true);
    }

    public function canResumeQuote(): bool
    {
        return $this->isCancelled();
    }

    public function canMarkSentToClientForAuthorization(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        return $this->authorization_status === CommercialQuoteAuthorizationStatus::Authorized
            && $this->activeWorkflowStep() === CommercialQuoteWorkflowStep::InternallyAuthorized;
    }

    public function canMarkClientAuthorized(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        return $this->authorization_status === CommercialQuoteAuthorizationStatus::Authorized
            && $this->activeWorkflowStep() === CommercialQuoteWorkflowStep::AwaitingClientAuthorization;
    }

    public function canMarkRequiresNegotiationOrRevision(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        return $this->authorization_status === CommercialQuoteAuthorizationStatus::Authorized
            && $this->activeWorkflowStep() === CommercialQuoteWorkflowStep::AwaitingClientAuthorization;
    }

    public function canRequestAuthorization(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

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
