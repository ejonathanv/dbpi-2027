<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AppModule;
use App\Enums\CrmRecordScope;
use App\Enums\ModulePermissionLevel;
use App\Enums\UserType;
use App\Models\Company;
use Filament\Forms\Components\CheckboxList;
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
                ->native(false)
                ->live()
                ->disabled(fn (Get $get): bool => ! self::isUsuarioType($get)))
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
                            ->default(UserType::Usuario->value)
                            ->required()
                            ->native(false)
                            ->live(),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
                Section::make('Permisos por módulo')
                    ->columnSpanFull()
                    ->columns(2)
                    ->visible(fn (Get $get): bool => ! self::isContactoType($get))
                    ->description(function (Get $get): string {
                        if (self::isAdministratorType($get)) {
                            return 'Los administradores tienen acceso completo a todos los módulos; no requieren asignación individual.';
                        }

                        return 'Define el nivel de acceso por cada módulo (ver, editar o eliminar).';
                    })
                    ->schema([
                        ...$modulePermissionFields,
                        Placeholder::make('permisos_admin_hint')
                            ->label('')
                            ->visible(fn (Get $get): bool => self::isAdministratorType($get))
                            ->content('Cambia el tipo a «Usuario» para asignar permisos granulares por módulo.')
                            ->columnSpanFull(),
                    ]),
                Section::make('CRM — alcance de datos')
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => self::isUsuarioType($get) && self::hasCrmModulePermission($get))
                    ->description('Limita qué oportunidades y eventos del calendario puede ver este usuario dentro del módulo CRM.')
                    ->schema([
                        Select::make('crm_record_scope')
                            ->label('Oportunidades y eventos')
                            ->options(CrmRecordScope::class)
                            ->default(CrmRecordScope::All)
                            ->required()
                            ->native(false),
                    ]),
                Section::make('Empresas — Recursos Humanos')
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => self::isUsuarioType($get) && self::hasRhModulePermission($get))
                    ->description('El usuario solo podrá ver y operar sobre las empresas seleccionadas dentro del módulo RH.')
                    ->schema([
                        CheckboxList::make('company_ids')
                            ->label('Empresas con acceso')
                            ->options(fn (): array => Company::query()
                                ->orderBy('legal_name')
                                ->pluck('legal_name', 'id')
                                ->all())
                            ->columns(2)
                            ->bulkToggleable()
                            ->searchable(),
                    ]),
                Section::make('Contacto externo')
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => self::isContactoType($get))
                    ->schema([
                        Placeholder::make('contacto_info')
                            ->label('')
                            ->content('El acceso al portal y los permisos de contacto se configurarán cuando estén disponibles los módulos CRM y Helpdesk.'),
                    ]),
            ]);
    }

    public static function resolveUserTypeState(mixed $state): ?UserType
    {
        if ($state instanceof UserType) {
            return $state;
        }

        if (is_string($state) && $state !== '') {
            return UserType::tryFrom($state);
        }

        return null;
    }

    public static function isUsuarioType(Get $get): bool
    {
        $type = self::resolveUserTypeState($get('user_type'));

        if ($type !== null) {
            return $type === UserType::Usuario;
        }

        return true;
    }

    public static function isAdministratorType(Get $get): bool
    {
        $type = self::resolveUserTypeState($get('user_type'));

        return $type === UserType::Administrador;
    }

    public static function isContactoType(Get $get): bool
    {
        $type = self::resolveUserTypeState($get('user_type'));

        return $type === UserType::Contacto;
    }

    public static function hasRhModulePermission(Get $get): bool
    {
        return filled($get('module_permissions.'.AppModule::Rh->value));
    }

    public static function hasCrmModulePermission(Get $get): bool
    {
        return filled($get('module_permissions.'.AppModule::Crm->value));
    }
}
