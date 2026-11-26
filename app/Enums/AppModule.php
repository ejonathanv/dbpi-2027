<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AppModule: string implements HasLabel
{
    case Rh = 'rh';
    case Comercial = 'comercial';
    case Helpdesk = 'helpdesk';
    case Crm = 'crm';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rh => 'Recursos Humanos',
            self::Comercial => 'Comercial',
            self::Helpdesk => 'Helpdesk',
            self::Crm => 'CRM',
        };
    }

    public function navigationGroup(): string
    {
        return $this->getLabel();
    }
}