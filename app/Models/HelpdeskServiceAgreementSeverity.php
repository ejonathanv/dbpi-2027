<?php

namespace App\Models;

use App\Enums\HelpdeskSlaResponseUnit;
use App\Enums\HelpdeskTicketSeverity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpdeskServiceAgreementSeverity extends Model
{
    protected $table = 'helpdesk_service_agreement_severities';

    protected $fillable = [
        'helpdesk_service_agreement_id',
        'severity',
        'response_number',
        'response_unit',
    ];

    protected function casts(): array
    {
        return [
            'severity' => HelpdeskTicketSeverity::class,
            'response_number' => 'integer',
            'response_unit' => HelpdeskSlaResponseUnit::class,
        ];
    }

    public function serviceAgreement(): BelongsTo
    {
        return $this->belongsTo(HelpdeskServiceAgreement::class, 'helpdesk_service_agreement_id');
    }

    public function responseTimeLabel(): string
    {
        return sprintf(
            '%d %s',
            $this->response_number,
            $this->response_unit?->getLabel() ?? ''
        );
    }
}
