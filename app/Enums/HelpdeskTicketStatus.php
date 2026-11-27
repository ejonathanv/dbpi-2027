<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum HelpdeskTicketStatus: string implements HasColor, HasLabel
{
    case Abierto = 'abierto';
    case EnProceso = 'en_proceso';
    case EnEsperaCliente = 'en_espera_cliente';
    case Monitoreo = 'monitoreo';
    case Resuelto = 'resuelto';
    case Cerrado = 'cerrado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Abierto => 'Abierto',
            self::EnProceso => 'En proceso',
            self::EnEsperaCliente => 'En espera del cliente',
            self::Monitoreo => 'Monitoreo',
            self::Resuelto => 'Resuelto',
            self::Cerrado => 'Cerrado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Abierto => 'info',
            self::EnProceso => 'warning',
            self::EnEsperaCliente => 'gray',
            self::Monitoreo => 'primary',
            self::Resuelto => 'success',
            self::Cerrado => 'gray',
        };
    }
}
