<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CrmOpportunityStage: string implements HasLabel, HasColor
{
    case Prospeccion = 'prospeccion';
    case CotizacionEnProceso = 'cotizacion_en_proceso';
    case AutorizacionInterna = 'autorizacion_interna';
    case CotizacionEnviada = 'cotizacion_enviada';
    case NegociacionRevision = 'negociacion_revision';
    case CerradoGanado = 'cerrado_ganado';
    case CerradoPerdido = 'cerrado_perdido';

    public function getLabel(): string
    {
        return match ($this) {
            self::Prospeccion => 'Prospección',
            self::CotizacionEnProceso => 'Cotización en proceso',
            self::AutorizacionInterna => 'Autorización interna',
            self::CotizacionEnviada => 'Cotización enviada',
            self::NegociacionRevision => 'Negociación/Revisión',
            self::CerradoGanado => 'Cerrado Ganado',
            self::CerradoPerdido => 'Cerrado Perdido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Prospeccion => 'gray',
            self::CotizacionEnProceso => 'info',
            self::AutorizacionInterna => 'warning',
            self::CotizacionEnviada => 'primary',
            self::NegociacionRevision => 'info',
            self::CerradoGanado => 'success',
            self::CerradoPerdido => 'danger',
        };
    }

    public function syncPriority(): int
    {
        return match ($this) {
            self::Prospeccion => 10,
            self::CotizacionEnProceso => 20,
            self::AutorizacionInterna => 30,
            self::CotizacionEnviada => 40,
            self::NegociacionRevision => 50,
            self::CerradoPerdido => 85,
            self::CerradoGanado => 90,
        };
    }
}