<?php

namespace App\Filament\Resources\CrmOpportunities\Schemas;

use App\Enums\CrmOpportunitySource;
use App\Enums\CrmOpportunityStage;
use App\Enums\SalaryCurrency;
use App\Models\CrmAccount;
use App\Models\CrmContact;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CrmOpportunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('General')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre de la oportunidad')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(4)
                            ->columnSpanFull(),
                        Select::make('crm_account_id')
                            ->label('Cuenta relacionada')
                            ->options(fn (): array => CrmAccount::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('crm_contact_id', null))
                            ->native(false),
                        Select::make('crm_contact_id')
                            ->label('Contacto')
                            ->options(function (Get $get): array {
                                $accountId = $get('crm_account_id');

                                if (blank($accountId)) {
                                    return [];
                                }

                                return CrmContact::query()
                                    ->where('crm_account_id', $accountId)
                                    ->orderBy('first_name')
                                    ->get()
                                    ->mapWithKeys(fn (CrmContact $c): array => [$c->getKey() => $c->fullName()])
                                    ->all();
                            })
                            ->searchable()
                            ->native(false),
                        Select::make('stage')
                            ->label('Etapa')
                            ->options(CrmOpportunityStage::class)
                            ->required()
                            ->native(false),
                        Select::make('source')
                            ->label('Origen')
                            ->options(CrmOpportunitySource::class)
                            ->native(false),
                        Select::make('assigned_user_id')
                            ->label('Responsable')
                            ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->native(false),
                    ]),
                Section::make('Valor y cierre')
                    ->columns(2)
                    ->schema([
                        TextInput::make('amount')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(0),
                        Select::make('currency')
                            ->label('Moneda')
                            ->options(SalaryCurrency::class)
                            ->default(SalaryCurrency::Mxn)
                            ->required()
                            ->native(false),
                        TextInput::make('probability_percent')
                            ->label('Probabilidad')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        DatePicker::make('expected_close_date')
                            ->label('Fecha de cierre')
                            ->native(false),
                        TextInput::make('fob')
                            ->label('FOB')
                            ->placeholder('Ej. Tijuana, Mexicali')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Section::make('IVA')
                    ->columns(2)
                    ->schema([
                        Toggle::make('use_account_vat')
                            ->label('Usar IVA de la cuenta')
                            ->default(true)
                            ->live()
                            ->helperText(fn (Get $get): ?string => $get('use_account_vat')
                                ? self::accountVatHint($get('crm_account_id'))
                                : null)
                            ->columnSpanFull(),
                        TextInput::make('vat_treatment')
                            ->label('IVA personalizado')
                            ->visible(fn (Get $get): bool => ! $get('use_account_vat'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Section::make('Entrega')
                    ->columns(2)
                    ->schema([
                        TextInput::make('estimated_delivery_time')
                            ->label('Tiempo estimado de entrega')
                            ->placeholder('Ej. 4 semanas, 30 días hábiles')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('use_account_delivery_days')
                            ->label('Usar días de entrega de la cuenta')
                            ->default(true)
                            ->live()
                            ->helperText(fn (Get $get): ?string => $get('use_account_delivery_days')
                                ? self::accountDeliveryHint($get('crm_account_id'))
                                : null)
                            ->columnSpanFull(),
                        Toggle::make('allows_partial_deliveries')
                            ->label('¿Entregas parciales?')
                            ->visible(fn (Get $get): bool => ! $get('use_account_delivery_days')),
                        TextInput::make('delivery_days')
                            ->label('Días de entrega')
                            ->placeholder('Ej. Lunes a viernes')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => ! $get('use_account_delivery_days')),
                        Textarea::make('delivery_schedule')
                            ->label('Horarios de entrega')
                            ->rows(2)
                            ->visible(fn (Get $get): bool => ! $get('use_account_delivery_days'))
                            ->columnSpanFull(),
                    ]),
                Section::make('Condiciones de pago')
                    ->columns(2)
                    ->schema([
                        Toggle::make('use_account_payment_terms')
                            ->label('Usar condiciones de pago de la cuenta')
                            ->default(true)
                            ->live()
                            ->helperText(fn (Get $get): ?string => $get('use_account_payment_terms')
                                ? self::accountPaymentHint($get('crm_account_id'))
                                : null)
                            ->columnSpanFull(),
                        TextInput::make('advance_percent')
                            ->label('% anticipo')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn (Get $get): bool => ! $get('use_account_payment_terms')),
                        TextInput::make('advance_days')
                            ->label('Días (anticipo)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => ! $get('use_account_payment_terms')),
                        TextInput::make('on_delivery_percent')
                            ->label('% al entregar')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn (Get $get): bool => ! $get('use_account_payment_terms')),
                        TextInput::make('on_delivery_days')
                            ->label('Días (al entregar)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => ! $get('use_account_payment_terms')),
                        TextInput::make('installation_percent')
                            ->label('% instalación')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn (Get $get): bool => ! $get('use_account_payment_terms')),
                        TextInput::make('installation_days')
                            ->label('Días (instalación)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => ! $get('use_account_payment_terms')),
                    ]),
            ]);
    }

    protected static function accountVatHint(mixed $accountId): ?string
    {
        if (blank($accountId)) {
            return null;
        }

        $vat = CrmAccount::query()->whereKey($accountId)->value('vat_treatment');

        if (blank($vat)) {
            return 'La cuenta no tiene IVA definido.';
        }

        return "IVA de la cuenta: {$vat}";
    }

    protected static function accountPaymentHint(mixed $accountId): ?string
    {
        if (blank($accountId)) {
            return null;
        }

        $account = CrmAccount::query()->whereKey($accountId)->first();

        if (! $account) {
            return null;
        }

        $parts = array_filter([
            filled($account->advance_percent) ? "Anticipo {$account->advance_percent}%" : null,
            filled($account->on_delivery_percent) ? "Entrega {$account->on_delivery_percent}%" : null,
            filled($account->installation_percent) ? "Instalación {$account->installation_percent}%" : null,
        ]);

        if ($parts === []) {
            return 'La cuenta no tiene esquema de pagos definido.';
        }

        return 'Cuenta: '.implode(' · ', $parts);
    }

    protected static function accountDeliveryHint(mixed $accountId): ?string
    {
        if (blank($accountId)) {
            return null;
        }

        $account = CrmAccount::query()->whereKey($accountId)->first();

        if (! $account) {
            return null;
        }

        $parts = array_filter([
            filled($account->delivery_days) ? "Días: {$account->delivery_days}" : null,
            filled($account->delivery_schedule) ? 'Con horarios definidos' : null,
            $account->allows_partial_deliveries ? 'Entregas parciales' : null,
        ]);

        if ($parts === []) {
            return 'La cuenta no tiene días de entrega definidos.';
        }

        return 'Cuenta: '.implode(' · ', $parts);
    }
}
