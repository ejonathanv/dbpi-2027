<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum HelpdeskSlaResponseUnit: string implements HasLabel
{
    case Minutos = 'minutos';
    case Horas = 'horas';
    case Dias = 'dias';
    case Meses = 'meses';

    public function getLabel(): string
    {
        return match ($this) {
            self::Minutos => 'Minutos',
            self::Horas => 'Horas',
            self::Dias => 'Días',
            self::Meses => 'Meses',
        };
    }
}
