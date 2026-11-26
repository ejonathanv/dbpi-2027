<?php

namespace App\Models;

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
        'expected_close_date',
    ];

    protected function casts(): array
    {
        return [
            'stage' => CrmOpportunityStage::class,
            'amount' => 'decimal:2',
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
}
