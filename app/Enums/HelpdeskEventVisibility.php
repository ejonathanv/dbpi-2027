<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum HelpdeskEventVisibility: string implements HasLabel
{
    case Privado = 'privado';
    case Publico = 'publico';

    public function getLabel(): string
    {
        return match ($this) {
            self::Privado => 'Privado',
            self::Publico => 'Público',
        };
    }
}
