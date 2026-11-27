<?php

namespace App\Filament\Pages\Modules;

use App\Enums\AppModule;

class ComercialModuleOverview extends ModuleOverviewPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 1;

    public static function module(): AppModule
    {
        return AppModule::Comercial;
    }
}
