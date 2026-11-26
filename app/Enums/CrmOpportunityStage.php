<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CrmOpportunityStage: string implements HasLabel
{
    case Prospecto = 'prospecto';
    case Calificacion = 'calificacion';
    case Propuesta = 'propuesta';
    case Negociacion = 'negociacion';
    case Ganada = 'ganada';
    case Perdida = 'perdida';

    public function getLabel(): string
    {
        return match ($this) {
            self::Prospecto => 'Prospecto',
            self::Calificacion => 'Calificación',
            self::Propuesta => 'Propuesta',
            self::Negociacion => 'Negociación',
            self::Ganada => 'Ganada',
            self::Perdida => 'Perdida',
        };
    }
}
