<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class EscritorioDashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Escritorio';

    protected static ?string $title = 'Escritorio';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = -2;
}
