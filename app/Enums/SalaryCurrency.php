<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SalaryCurrency: string implements HasLabel
{
    case Mxn = 'MXN';
    case Usd = 'USD';

    public function getLabel(): string
    {
        return match ($this) {
            self::Mxn => 'Peso mexicano (MXN)',
            self::Usd => 'Dólar (USD)',
        };
    }
}