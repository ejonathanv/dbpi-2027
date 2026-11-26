<?php

namespace App\Filament\Resources\CrmAccounts\Schemas;

use App\Enums\CrmAccountType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CrmAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Cuenta')
                    ->tabs([
                        Tab::make('General')
                            ->schema(self::generalTab()),
                        Tab::make('Facturación y fiscal')
                            ->schema(self::billingTab()),
                        Tab::make('Condiciones de pago y entrega')
                            ->schema(self::paymentTermsTab()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /** @return array<int, mixed> */
    protected static function generalTab(): array
    {
        return [
            Section::make()
                ->columns(2)
                ->schema([
                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->disk('public')
                        ->directory('crm/accounts/logos')
                        ->visibility('public')
                        ->imageEditor()
                        ->columnSpanFull(),
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),
                    Select::make('account_type')
                        ->label('Tipo')
                        ->options(CrmAccountType::class)
                        ->default(CrmAccountType::Cliente)
                        ->required()
                        ->native(false),
                    TextInput::make('legal_name')
                        ->label('Razón social')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('general_description')
                        ->label('Descripción general')
                        ->rows(4)
                        ->columnSpanFull(),
                    TextInput::make('primary_email')
                        ->label('E-mail principal')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(30),
                    TextInput::make('industry')
                        ->label('Industria')
                        ->maxLength(255),
                    TextInput::make('sic_code')
                        ->label('Código SIC')
                        ->maxLength(20),
                    Toggle::make('is_active')
                        ->label('Activa')
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ];
    }

    /** @return array<int, mixed> */
    protected static function billingTab(): array
    {
        return [
            Section::make('Direcciones y contacto fiscal')
                ->columns(2)
                ->schema([
                    Textarea::make('billing_address')
                        ->label('Dirección de facturación')
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('delivery_address')
                        ->label('Dirección de entrega')
                        ->rows(3)
                        ->columnSpanFull(),
                    TextInput::make('billing_email')
                        ->label('E-mail de facturación')
                        ->email()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
            Section::make('Datos fiscales y comerciales')
                ->columns(2)
                ->schema([
                    TextInput::make('vat_treatment')
                        ->label('IVA')
                        ->helperText('Ej. Responsable, Exento, Tasa 16 %, etc.')
                        ->maxLength(255),
                    TextInput::make('payment_method')
                        ->label('Método de pago')
                        ->helperText('Catálogo SAT (PUE, PPD, etc.)')
                        ->maxLength(100),
                    TextInput::make('payment_form')
                        ->label('Forma de pago')
                        ->helperText('Catálogo SAT (01 Efectivo, 03 Transferencia, etc.)')
                        ->maxLength(100),
                    TextInput::make('cfdi_use')
                        ->label('Uso de CFDI')
                        ->helperText('Ej. G03 Gastos en general')
                        ->maxLength(100),
                    TextInput::make('institutional_discount_percent')
                        ->label('% descuento institucional')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->default(0),
                    Textarea::make('accounts_payable_info')
                        ->label('Información de cuentas por pagar')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ];
    }

    /** @return array<int, mixed> */
    protected static function paymentTermsTab(): array
    {
        return [
            Section::make('Entrega')
                ->columns(2)
                ->schema([
                    Toggle::make('allows_partial_deliveries')
                        ->label('¿Entregas parciales?')
                        ->default(false),
                    TextInput::make('delivery_days')
                        ->label('Días de entrega')
                        ->maxLength(255)
                        ->placeholder('Ej. Lunes a viernes'),
                    Textarea::make('delivery_schedule')
                        ->label('Horarios de entrega')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
            Section::make('Esquema de pagos')
                ->columns(2)
                ->schema([
                    TextInput::make('advance_percent')
                        ->label('% anticipo')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),
                    TextInput::make('advance_days')
                        ->label('Días (anticipo)')
                        ->numeric()
                        ->integer()
                        ->minValue(0),
                    TextInput::make('on_delivery_percent')
                        ->label('% al entregar')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),
                    TextInput::make('on_delivery_days')
                        ->label('Días (al entregar)')
                        ->numeric()
                        ->integer()
                        ->minValue(0),
                    TextInput::make('installation_percent')
                        ->label('% instalación')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),
                    TextInput::make('installation_days')
                        ->label('Días (instalación)')
                        ->numeric()
                        ->integer()
                        ->minValue(0),
                ]),
        ];
    }
}
