<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'legal_name',
        'trade_name',
        'rfc',
        'tax_regime',
        'address',
        'contact_phone',
        'contact_email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(CompanyBranch::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(CompanyDepartment::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(CompanyPosition::class);
    }
}
