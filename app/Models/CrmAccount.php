<?php

namespace App\Models;

use App\Enums\CrmAccountType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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

    public static function normalizeLogoPath(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_array($value)) {
            $value = Arr::first(
                array_values(array_filter(Arr::flatten($value), fn (mixed $path): bool => is_string($path) && filled($path))),
            );
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if (str_starts_with($value, '[') || str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = Arr::first(
                    array_values(array_filter(Arr::flatten($decoded), fn (mixed $path): bool => is_string($path) && filled($path))),
                );
            }
        }

        if (! is_string($value) || blank($value)) {
            return null;
        }

        if (str_starts_with($value, '/storage/')) {
            $value = Str::after($value, '/storage/');
        }

        if (str_starts_with($value, 'storage/')) {
            $value = Str::after($value, 'storage/');
        }

        return $value;
    }

    protected function logoPath(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => self::normalizeLogoPath($value),
            set: fn (mixed $value): ?string => self::normalizeLogoPath($value),
        );
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

    public function helpdeskContracts(): BelongsToMany
    {
        return $this->belongsToMany(
            HelpdeskContract::class,
            'crm_account_helpdesk_contract',
        )
            ->withPivot(['starts_at', 'ends_at'])
            ->withTimestamps();
    }

    public function helpdeskCoverages(): BelongsToMany
    {
        return $this->belongsToMany(
            HelpdeskCoverage::class,
            'crm_account_helpdesk_coverage',
        )->withTimestamps();
    }

    public function helpdeskServiceAgreements(): BelongsToMany
    {
        return $this->belongsToMany(
            HelpdeskServiceAgreement::class,
            'crm_account_helpdesk_service_agreement',
        )->withTimestamps();
    }
}
