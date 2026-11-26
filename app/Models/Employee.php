<?php

namespace App\Models;

use App\Enums\EmployeeGender;
use App\Enums\EmployeeStatus;
use App\Enums\MaritalStatus;
use App\Enums\SalaryCurrency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'second_last_name',
        'curp',
        'rfc',
        'nss',
        'birth_date',
        'gender',
        'marital_status',
        'email',
        'phone',
        'address',
        'hire_date',
        'termination_date',
        'employee_number',
        'status',
        'monthly_salary',
        'salary_currency',
        'company_position_id',
        'company_branch_id',
        'company_department_id',
        'bank_name',
        'bank_account_number',
        'bank_card_number',
        'bank_clabe',
        'imss_daily_sbc',
        'payroll_discounts_notes',
        'vacation_days_per_year',
        'vacation_days_used',
        'vacation_notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'hire_date' => 'date',
            'termination_date' => 'date',
            'status' => EmployeeStatus::class,
            'gender' => EmployeeGender::class,
            'marital_status' => MaritalStatus::class,
            'salary_currency' => SalaryCurrency::class,
            'monthly_salary' => 'decimal:2',
            'imss_daily_sbc' => 'decimal:2',
            'vacation_days_per_year' => 'integer',
            'vacation_days_used' => 'integer',
        ];
    }

    public static function suggestedEmployeeNumberForCompany(int|string $companyId): string
    {
        $max = 0;

        static::query()
            ->where('company_id', $companyId)
            ->whereNotNull('employee_number')
            ->pluck('employee_number')
            ->each(function (mixed $number) use (&$max): void {
                $number = (string) $number;

                if (preg_match('/^\d+$/', $number)) {
                    $max = max($max, (int) $number);
                }
            });

        return (string) ($max + 1);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(CompanyBranch::class, 'company_branch_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(CompanyDepartment::class, 'company_department_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(CompanyPosition::class, 'company_position_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(EmployeePhoto::class)->orderBy('sort_order');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EmployeeAttachment::class)->latest();
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
            $this->second_last_name,
        ])));
    }

    public function vacationDaysRemaining(): ?int
    {
        if ($this->vacation_days_per_year === null) {
            return null;
        }

        return max(0, $this->vacation_days_per_year - ($this->vacation_days_used ?? 0));
    }
}
