<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CrmRecordScope: string implements HasLabel
{
    case All = 'all';
    case Own = 'own';

    public function getLabel(): string
    {
        return match ($this) {
            self::All => 'Ver todo el CRM (oportunidades y eventos)',
            self::Own => 'Solo oportunidades y eventos asignados a este usuario',
        };
    }
}
