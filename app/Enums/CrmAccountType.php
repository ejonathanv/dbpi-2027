<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CrmAccountType: string implements HasLabel
{
    case Cliente = 'cliente';
    case Socio = 'socio';
    case Inversor = 'inversor';
    case Revendedor = 'revendedor';
    case Proveedor = 'proveedor';
    case Prospecto = 'prospecto';
    case Otro = 'otro';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cliente => 'Cliente',
            self::Socio => 'Socio',
            self::Inversor => 'Inversor',
            self::Revendedor => 'Revendedor',
            self::Proveedor => 'Proveedor',
            self::Prospecto => 'Prospecto',
            self::Otro => 'Otro',
        };
    }
}
