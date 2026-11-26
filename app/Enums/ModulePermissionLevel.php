<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ModulePermissionLevel: string implements HasLabel
{
    case View = 'view';
    case Edit = 'edit';
    case Delete = 'delete';

    public function getLabel(): string
    {
        return match ($this) {
            self::View => 'Ver',
            self::Edit => 'Editar',
            self::Delete => 'Eliminar',
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::View => 1,
            self::Edit => 2,
            self::Delete => 3,
        };
    }

    public function satisfies(self $required): bool
    {
        return $this->rank() >= $required->rank();
    }
}