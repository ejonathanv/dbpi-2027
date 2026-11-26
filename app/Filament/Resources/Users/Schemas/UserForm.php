<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AppModule;
use App\Enums\ModulePermissionLevel;
use App\Enums\UserType;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $modulePermissionFields = collect(AppModule::cases())
            ->map(fn (AppModule $module) => Select::make('module_permissions.'.$module->value)
                ->label($module->getLabel())
                ->options(ModulePermissionLevel::class)
                ->placeholder('Sin acceso')
                ->native(false))
            ->all();

        return $schema
            ->columns(2)
            ->components([
                Section::make('Datos de la cuenta')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                        Select::make('user_type')
                            ->label('Tipo de usuario')
                            ->options(UserType::class)
                            ->default(UserType::Usuario)
                            ->required()
                            ->native(false)
                            ->live(),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
                Section::make('Permisos por módulo')
                    ->description('Solo aplica a usuarios tipo «Usuario».')
                    ->columnSpanFull()
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('user_type') === UserType::Usuario->value)
                    ->schema($modulePermissionFields),
                Section::make('Contacto externo')
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => $get('user_type') === UserType::Contacto->value)
                    ->schema([
                        Placeholder::make('contacto_info')
                            ->label('')
                            ->content('El acceso al portal y los permisos de contacto se configurarán cuando estén disponibles los módulos CRM y Helpdesk.'),
                    ]),
            ]);
    }
}