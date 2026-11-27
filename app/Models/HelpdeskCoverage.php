<?php

namespace App\Models;

use App\Support\WeekdayOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpdeskCoverage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'full_week',
        'weekdays',
        'all_day',
        'coverage_start',
        'coverage_end',
    ];

    protected function casts(): array
    {
        return [
            'full_week' => 'boolean',
            'weekdays' => 'array',
            'all_day' => 'boolean',
        ];
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(
            CrmAccount::class,
            'crm_account_helpdesk_coverage',
        )->withTimestamps();
    }

    public function weekdaysLabel(): string
    {
        if ($this->full_week) {
            return 'Toda la semana';
        }

        $days = collect($this->weekdays ?? [])
            ->map(fn (int $day): string => WeekdayOptions::labels()[$day] ?? (string) $day)
            ->values()
            ->all();

        return $days !== [] ? implode(', ', $days) : '—';
    }

    public function scheduleLabel(): string
    {
        if ($this->all_day) {
            return 'Todo el día';
        }

        if (blank($this->coverage_start) || blank($this->coverage_end)) {
            return '—';
        }

        return sprintf(
            '%s – %s',
            substr((string) $this->coverage_start, 0, 5),
            substr((string) $this->coverage_end, 0, 5)
        );
    }
}
