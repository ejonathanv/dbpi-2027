<?php

namespace App\Filament\Pages;

use App\Enums\PanelNavigationGroup;
use Filament\Pages\Dashboard as BaseDashboard;
use UnitEnum;

class EscritorioDashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Escritorio';

    protected static ?string $title = 'Escritorio';

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Escritorio;

    protected static ?int $navigationSort = 1;
}
