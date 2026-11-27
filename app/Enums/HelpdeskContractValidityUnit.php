<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum HelpdeskContractValidityUnit: string implements HasLabel
{
    case Dias = 'dias';
    case Semanas = 'semanas';
    case Meses = 'meses';
    case Anios = 'anios';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dias => 'Días',
            self::Semanas => 'Semanas',
            self::Meses => 'Meses',
            self::Anios => 'Años',
        };
    }
}
