<?php

namespace App\Models;

use App\Enums\HelpdeskTicketOpenedByType;
use App\Enums\HelpdeskTicketSeverity;
use App\Enums\HelpdeskTicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpdeskTicket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'crm_account_id',
        'subject',
        'description',
        'opened_at',
        'opened_by_type',
        'opened_by_user_id',
        'opened_by_contact_id',
        'helpdesk_category_id',
        'status',
        'severity',
        'company_id',
        'company_department_id',
        'employee_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'opened_by_type' => HelpdeskTicketOpenedByType::class,
            'status' => HelpdeskTicketStatus::class,
            'severity' => HelpdeskTicketSeverity::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (HelpdeskTicket $ticket): void {
            $ticket->created_by ??= auth()->id();
            $ticket->opened_at ??= now();
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CrmAccount::class, 'crm_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(HelpdeskCategory::class, 'helpdesk_category_id');
    }

    public function openedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function openedByContact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'opened_by_contact_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(CompanyDepartment::class, 'company_department_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(HelpdeskTicketEvent::class)->latest();
    }

    public function openedByLabel(): string
    {
        return match ($this->opened_by_type) {
            HelpdeskTicketOpenedByType::User => $this->openedByUser?->name ?? '—',
            HelpdeskTicketOpenedByType::Contact => $this->openedByContact?->fullName() ?? '—',
        };
    }
}
