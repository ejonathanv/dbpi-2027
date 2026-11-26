<?php

namespace App\Filament\Pages\Modules;

use App\Enums\AppModule;
use App\Models\User;
use App\Support\ModuleAccess;
use Filament\Pages\Page;

abstract class ModuleOverviewPage extends Page
{
    protected string $view = 'filament.pages.module-overview';

    public static function getNavigationGroup(): ?string
    {
        return static::module()->navigationGroup();
    }

    public static function getNavigationLabel(): string
    {
        return 'Resumen';
    }

    abstract public static function module(): AppModule;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && ModuleAccess::canViewModule($user, static::module());
    }

    protected function getViewData(): array
    {
        return [
            'moduleLabel' => static::module()->getLabel(),
        ];
    }
}