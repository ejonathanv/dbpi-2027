<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserType: string implements HasLabel
{
    case Usuario = 'usuario';
    case Administrador = 'administrador';
    case Contacto = 'contacto';

    public function getLabel(): string
    {
        return match ($this) {
            self::Usuario => 'Usuario',
            self::Administrador => 'Administrador',
            self::Contacto => 'Contacto',
        };
    }
}