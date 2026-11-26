<?php

namespace App\Models;

use App\Enums\EmployeeGender;
use App\Enums\EmployeeStatus;
use App\Enums\MaritalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        ];
    }

    public static function suggestedEmployeeNumberForCompany(int | string $companyId): string
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

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
            $this->second_last_name,
        ])));
    }
}