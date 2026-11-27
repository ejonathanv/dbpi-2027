<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpdeskServiceAgreement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'crm_account_id',
        'name',
        'description',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(CrmAccount::class, 'crm_account_id');
    }

    public function severityResponses(): HasMany
    {
        return $this->hasMany(HelpdeskServiceAgreementSeverity::class);
    }
}
