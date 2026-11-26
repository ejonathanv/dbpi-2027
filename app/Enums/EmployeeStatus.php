<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmployeeStatus: string implements HasLabel
{
    case Activo = 'activo';
    case Baja = 'baja';
    case Suspendido = 'suspendido';

    public function getLabel(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Baja => 'Baja',
            self::Suspendido => 'Suspendido',
        };
    }
}