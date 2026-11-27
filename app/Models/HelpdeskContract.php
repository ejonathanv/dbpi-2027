<?php

namespace App\Models;

use App\Enums\HelpdeskContractValidityUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        )->withTimestamps();
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
