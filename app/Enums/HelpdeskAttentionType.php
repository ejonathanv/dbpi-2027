<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum HelpdeskAttentionType: string implements HasLabel
{
    case Remota = 'remota';
    case EnSitio = 'en_sitio';

    public function getLabel(): string
    {
        return match ($this) {
            self::Remota => 'Remota',
            self::EnSitio => 'En sitio',
        };
    }
}
