<?php

namespace App\Filament\Pages;

use App\Enums\PanelNavigationGroup;
use App\Models\SystemSetting;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ExchangeRateSettingsPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Sistema;

    protected static ?string $navigationLabel = 'Tipo de cambio';

    protected static ?string $title = 'Tipo de cambio';

    protected static ?int $navigationSort = 15;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected string $view = 'filament.pages.exchange-rate-settings';

    public string $exchangeRate = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdministrator();
    }

    public function mount(): void
    {
        $this->exchangeRate = (string) SystemSetting::getDecimal('exchange_rate_usd_mxn', 18);
    }

    public function save(): void
    {
        $this->validate([
            'exchangeRate' => ['required', 'numeric', 'min:0'],
        ]);

        SystemSetting::set('exchange_rate_usd_mxn', $this->exchangeRate);

        Notification::make()
            ->title('Tipo de cambio actualizado')
            ->success()
            ->send();
    }
}
