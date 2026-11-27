<?php

namespace App\Filament\Pages;

use App\Enums\AppModule;
use App\Enums\PanelNavigationGroup;
use App\Models\CommercialQuoteAuthorizer;
use App\Models\User;
use App\Support\ModuleAccess;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class CommercialQuoteAuthorizersPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Comercial;

    protected static ?string $navigationLabel = 'Autorizadores';

    protected static ?string $title = 'Autorizadores de cotización';

    protected static ?int $navigationSort = 20;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected string $view = 'filament.pages.commercial-quote-authorizers';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && ModuleAccess::canEditModule($user, AppModule::Comercial);
    }

    public function mount(): void
    {
        $this->data['authorizerUserIds'] = CommercialQuoteAuthorizer::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->pluck('user_id')
            ->map(fn (int $id): int => $id)
            ->all();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Select::make('authorizerUserIds')
                    ->label('Autorizadores')
                    ->helperText('Usuarios que pueden validar cotizaciones comerciales.')
                    ->options(fn (): array => User::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->live()
                    ->columnSpanFull(),
            ]);
    }

    public function saveAuthorizers(): void
    {
        $ids = array_values(array_unique(array_map('intval', $this->data['authorizerUserIds'] ?? [])));

        CommercialQuoteAuthorizer::query()->delete();

        foreach ($ids as $userId) {
            CommercialQuoteAuthorizer::query()->create([
                'user_id' => $userId,
                'is_active' => true,
            ]);
        }

        Notification::make()
            ->title('Autorizadores guardados')
            ->success()
            ->send();
    }
}
