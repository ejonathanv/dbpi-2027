<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum HelpdeskTicketScope: string implements HasLabel
{
    case All = 'all';
    case AssignedOrOpened = 'assigned_or_opened';

    public function getLabel(): string
    {
        return match ($this) {
            self::All => 'Ver todos los tickets',
            self::AssignedOrOpened => 'Solo tickets abiertos o asignados a este usuario',
        };
    }
}
