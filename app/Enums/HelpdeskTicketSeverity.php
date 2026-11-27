<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum HelpdeskTicketSeverity: string implements HasColor, HasLabel
{
    case Alta = 'alta';
    case Normal = 'normal';
    case Baja = 'baja';

    public function getLabel(): string
    {
        return match ($this) {
            self::Alta => 'Alta',
            self::Normal => 'Normal',
            self::Baja => 'Baja',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Alta => 'danger',
            self::Normal => 'warning',
            self::Baja => 'success',
        };
    }
}
