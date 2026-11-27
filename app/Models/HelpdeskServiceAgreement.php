<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpdeskServiceAgreement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(
            CrmAccount::class,
            'crm_account_helpdesk_service_agreement',
        )->withTimestamps();
    }

    public function severityResponses(): HasMany
    {
        return $this->hasMany(HelpdeskServiceAgreementSeverity::class);
    }
}
