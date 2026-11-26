<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CrmOpportunitySource: string implements HasLabel
{
    case Llamada = 'llamada';
    case CorreoElectronico = 'correo';
    case Socio = 'socio';
    case RelacionesPublicas = 'relaciones_publicas';
    case SitioWeb = 'sitio_web';
    case Referido = 'referido';
    case Evento = 'evento';
    case Otro = 'otro';

    public function getLabel(): string
    {
        return match ($this) {
            self::Llamada => 'Llamada',
            self::CorreoElectronico => 'Correo electrónico',
            self::Socio => 'Socio',
            self::RelacionesPublicas => 'Relaciones públicas',
            self::SitioWeb => 'Sitio web',
            self::Referido => 'Referido',
            self::Evento => 'Evento',
            self::Otro => 'Otro',
        };
    }
}
