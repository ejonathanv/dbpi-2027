<?php

namespace App\Models;

use App\Enums\HelpdeskContractValidityUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpdeskContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'crm_account_id',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(CrmAccount::class, 'crm_account_id');
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
