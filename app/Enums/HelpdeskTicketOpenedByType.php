<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum HelpdeskTicketOpenedByType: string implements HasLabel
{
    case User = 'user';
    case Contact = 'contact';

    public function getLabel(): string
    {
        return match ($this) {
            self::User => 'Usuario del sistema',
            self::Contact => 'Contacto de la cuenta',
        };
    }
}
