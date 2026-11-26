<?php

namespace App\Filament\Pages\Modules;

use App\Enums\AppModule;

class ComercialModuleOverview extends ModuleOverviewPage
{
    protected static ?int $navigationSort = 1;

    public static function module(): AppModule
    {
        return AppModule::Comercial;
    }
}