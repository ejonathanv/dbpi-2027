<?php

namespace App\Filament\Resources\CommercialQuotes\Schemas;

use App\Enums\SalaryCurrency;
use App\Models\CrmAccount;
use App\Models\CrmContact;
use App\Models\SystemSetting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CommercialQuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Cotización')
                    ->tabs([
                        Tab::make('General')
                            ->schema(self::generalTab()),
                        Tab::make('Margen')
                            ->schema([
                                Placeholder::make('margen_pending')
                                    ->label('')
                                    ->content('Esta sección se configurará en una siguiente fase.'),
                            ]),
                        Tab::make('Categorías')
                            ->schema([
                                Placeholder::make('categories_pending')
                                    ->content('Esta sección se configurará en una siguiente fase.'),
                            ]),
                        Tab::make('Resumen')
                            ->schema([
                                Placeholder::make('summary_pending')
                                    ->content('Esta sección se configurará en una siguiente fase.'),
                            ]),
                        Tab::make('Autorizaciones')
                            ->schema([
                                Placeholder::make('auth_pending')
                                    ->content('Esta sección se configurará en una siguiente fase.'),
                            ]),
                        Tab::make('Comentarios')
                            ->schema([
                                Placeholder::make('comments_pending')
                                    ->content('Esta sección se configurará en una siguiente fase.'),
                            ]),
                        Tab::make('Archivos adjuntos')
                            ->schema([
                                Placeholder::make('files_pending')
                                    ->content('Esta sección se configurará en una siguiente fase.'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /** @return array<int, mixed> */
    protected static function generalTab(): array
    {
        return [
            Section::make('Relación CRM')
                ->columns(2)
                ->schema([
                    Select::make('crm_account_id')
                        ->label('Cuenta')
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
                                ->mapWithKeys(fn (CrmContact $contact): array => [
                                    $contact->getKey() => $contact->fullName(),
                                ])
                                ->all();
                        })
                        ->searchable()
                        ->native(false),
                    Textarea::make('payment_terms')
                        ->label('Términos')
                        ->rows(4)
                        ->columnSpanFull(),
                    TextInput::make('delivery_time')
                        ->label('Tiempo de entrega')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('project_description')
                        ->label('Descripción del proyecto')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
            Section::make('Contenido comercial')
                ->schema([
                    RichEditor::make('technical_summary')
                        ->label('Resumen técnico')
                        ->columnSpanFull(),
                    RichEditor::make('key_benefits')
                        ->label('Beneficios clave')
                        ->columnSpanFull(),
                    RichEditor::make('observations')
                        ->label('Observaciones')
                        ->columnSpanFull(),
                ]),
            Section::make('Condiciones de la cotización')
                ->columns(2)
                ->schema([
                    DatePicker::make('quoted_at')
                        ->label('Fecha de la cotización')
                        ->required()
                        ->native(false),
                    TextInput::make('validity_days')
                        ->label('Vigencia (días naturales)')
                        ->numeric()
                        ->integer()
                        ->required()
                        ->minValue(1)
                        ->default(30),
                    Select::make('presentation_currency')
                        ->label('Presentar cotización en')
                        ->options(SalaryCurrency::class)
                        ->required()
                        ->native(false),
                    TextInput::make('exchange_rate')
                        ->label('Tipo de cambio (USD → MXN)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->step(0.000001)
                        ->default(fn (): float => SystemSetting::getDecimal('exchange_rate_usd_mxn', 18)),
                    TextInput::make('fob')
                        ->label('FOB')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('general_discount_percent')
                        ->label('% de descuento general')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->default(0),
                    TextInput::make('vat_percent')
                        ->label('% de IVA')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),
                    Toggle::make('vat_included')
                        ->label('IVA incluido en la cotización')
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ];
    }
}
