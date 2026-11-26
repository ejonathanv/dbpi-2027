<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Orden fijo del menú lateral del panel (ver docs/ESPECIFICACIONES.md §9).
 */
enum PanelNavigationGroup: string implements HasLabel
{
    case Escritorio = 'Escritorio';
    case RecursosHumanos = 'Recursos Humanos';
    case Comercial = 'Comercial';
    case Crm = 'CRM';
    case Helpdesk = 'Helpdesk';
    case Sistema = 'Sistema';

    public function getLabel(): string
    {
        return $this->value;
    }

    /**
     * Grupos del menú lateral con submenú (excluye Escritorio, acceso directo en la raíz).
     *
     * @return array<int, self>
     */
    public static function sidebarGroups(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $group): bool => $group !== self::Escritorio,
        ));
    }
}
