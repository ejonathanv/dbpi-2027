<?php

namespace App\Models;

use App\Enums\CrmAccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'logo_path',
        'name',
        'general_description',
        'legal_name',
        'primary_email',
        'phone',
        'billing_address',
        'delivery_address',
        'billing_email',
        'vat_treatment',
        'payment_method',
        'payment_form',
        'cfdi_use',
        'institutional_discount_percent',
        'accounts_payable_info',
        'account_type',
        'sic_code',
        'industry',
        'allows_partial_deliveries',
        'delivery_days',
        'delivery_schedule',
        'advance_percent',
        'advance_days',
        'on_delivery_percent',
        'on_delivery_days',
        'installation_percent',
        'installation_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'account_type' => CrmAccountType::class,
            'institutional_discount_percent' => 'decimal:2',
            'advance_percent' => 'decimal:2',
            'on_delivery_percent' => 'decimal:2',
            'installation_percent' => 'decimal:2',
            'allows_partial_deliveries' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CrmContact::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(CrmOpportunity::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CrmCalendarEvent::class);
    }
}
