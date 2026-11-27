<?php

namespace App\Models;

use App\Enums\HelpdeskContractValidityUnit;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class HelpdeskContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'validity_number',
        'validity_unit',
    ];

    protected function casts(): array
    {
        return [
            'validity_number' => 'integer',
            'validity_unit' => HelpdeskContractValidityUnit::class,
        ];
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(
            CrmAccount::class,
            'crm_account_helpdesk_contract',
        )
            ->withPivot(['starts_at', 'ends_at'])
            ->withTimestamps();
    }

    public function computeEndsAt(CarbonInterface|string $startsAt): Carbon
    {
        $start = Carbon::parse($startsAt)->startOfDay();
        $number = $this->validity_number;
        $unit = $this->validity_unit;

        if ($number < 1 || ! $unit instanceof HelpdeskContractValidityUnit) {
            return $start->copy();
        }

        $periodEnd = match ($unit) {
            HelpdeskContractValidityUnit::Dias => $start->copy()->addDays($number),
            HelpdeskContractValidityUnit::Semanas => $start->copy()->addWeeks($number),
            HelpdeskContractValidityUnit::Meses => $start->copy()->addMonths($number),
            HelpdeskContractValidityUnit::Anios => $start->copy()->addYears($number),
        };

        return $periodEnd->subDay();
    }

    public function validityLabel(): string
    {
        return sprintf(
            '%d %s',
            $this->validity_number,
            $this->validity_unit?->getLabel() ?? ''
        );
    }
}
