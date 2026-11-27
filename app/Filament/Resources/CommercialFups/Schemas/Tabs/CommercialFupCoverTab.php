<?php

namespace App\Filament\Resources\CommercialFups\Schemas\Tabs;

use App\Models\CommercialFup;
use App\Models\User;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

class CommercialFupCoverTab
{
    /** @return array<int, mixed> */
    public static function schema(): array
    {
        return [
            Group::make(self::coverFields())
                ->columnSpanFull()
                ->extraAttributes([
                    'class' => 'fi-fup-cover-document w-full max-w-none rounded-xl border border-gray-200 bg-white p-4 text-[11px] shadow-sm dark:border-white/10 dark:bg-gray-900',
                ]),
        ];
    }

    /** @return array<int, mixed> */
    protected static function coverFields(): array
    {
        return [
            ViewField::make('cover_header')
                ->hiddenLabel()
                ->dehydrated(false)
                ->view('filament.resources.commercial-fups.partials.cover-form-header')
                ->viewData(fn (?CommercialFup $record): array => [
                    'fupNumber' => $record?->fup_number,
                ])
                ->columnSpanFull(),
            Section::make()
                ->hiddenLabel()
                ->columns(3)
                ->schema([
                    Placeholder::make('hdr_quote')
                        ->label('No. de cotización')
                        ->content(fn (?CommercialFup $record): string => (string) ($record?->quote?->quote_number ?? '—')),
                    TextInput::make('client_po_number')
                        ->label('No. orden del cliente')
                        ->maxLength(255)
                        ->disabled(self::locked()),
                    DatePicker::make('fup_date')
                        ->label('Fecha')
                        ->required()
                        ->native(false)
                        ->disabled(self::locked()),
                ])
                ->columnSpanFull()
                ->compact(),
            TextInput::make('cover_payload.trade_name')
                ->label('Nombre comercial')
                ->columnSpanFull()
                ->disabled(self::locked()),
            Textarea::make('cover_payload.project_description')
                ->label('Descripción del proyecto')
                ->rows(3)
                ->columnSpanFull()
                ->disabled(self::locked()),
            Section::make('Contacto responsable técnico')
                ->columns(3)
                ->schema([
                    TextInput::make('cover_payload.technical_contact_name')->label('Nombre')->disabled(self::locked()),
                    TextInput::make('cover_payload.technical_contact_email')->label('E-mail')->disabled(self::locked()),
                    TextInput::make('cover_payload.technical_contact_phone')->label('Teléfono')->disabled(self::locked()),
                ])
                ->columnSpanFull()
                ->compact(),
            Section::make('Embarque')
                ->schema([
                    TextInput::make('cover_payload.shipping.legal_name')->label('Razón social')->disabled(self::locked())->columnSpanFull(),
                    Textarea::make('cover_payload.shipping.street')->label('Domicilio')->rows(2)->disabled(self::locked())->columnSpanFull(),
                    TextInput::make('cover_payload.shipping.city_state')->label('Ciudad y estado')->disabled(self::locked()),
                    TextInput::make('cover_payload.shipping.postal_code')->label('C.P.')->disabled(self::locked()),
                    TextInput::make('cover_payload.shipping.partial_deliveries')->label('Entregas parciales (SI/NO)')->disabled(self::locked()),
                    TextInput::make('cover_payload.shipping.delivery_days')->label('Días de entrega')->disabled(self::locked()),
                    Textarea::make('cover_payload.shipping.delivery_schedule')->label('Horario de entregas')->rows(2)->disabled(self::locked())->columnSpanFull(),
                    Textarea::make('cover_payload.shipping.references')->label('Referencias')->rows(2)->disabled(self::locked())->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->compact(),
            Section::make('Facturación')
                ->schema([
                    TextInput::make('cover_payload.billing.legal_name')->label('Razón social')->disabled(self::locked())->columnSpanFull(),
                    Textarea::make('cover_payload.billing.street')->label('Domicilio')->rows(2)->disabled(self::locked())->columnSpanFull(),
                    TextInput::make('cover_payload.billing.city_state')->label('Ciudad y estado')->disabled(self::locked()),
                    TextInput::make('cover_payload.billing.rfc')->label('R.F.C.')->disabled(self::locked()),
                    TextInput::make('cover_payload.billing.postal_code')->label('C.P.')->disabled(self::locked()),
                    TextInput::make('cover_payload.billing.mub_percent')->label('MUB (%)')->disabled(self::locked()),
                    TextInput::make('cover_payload.billing.currency_code')->label('Moneda')->disabled(self::locked()),
                    TextInput::make('cover_payload.billing.exchange_rate')->label('Tipo de cambio')->disabled(self::locked()),
                ])
                ->columns(3)
                ->columnSpanFull()
                ->compact(),
            Section::make('Condiciones de pago')
                ->schema([
                    TextInput::make('cover_payload.payment_rows.0.percent')->label('Anticipo %')->disabled(self::locked()),
                    TextInput::make('cover_payload.payment_rows.0.days')->label('Anticipo días')->disabled(self::locked()),
                    TextInput::make('cover_payload.payment_rows.1.percent')->label('Entrega %')->disabled(self::locked()),
                    TextInput::make('cover_payload.payment_rows.1.days')->label('Entrega días')->disabled(self::locked()),
                    TextInput::make('cover_payload.payment_rows.2.percent')->label('Instalación %')->disabled(self::locked()),
                    TextInput::make('cover_payload.payment_rows.2.days')->label('Instalación días')->disabled(self::locked()),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->compact(),
            Section::make('Información del pedido · fechas de compromiso')
                ->schema([
                    TextInput::make('cover_payload.order_info.delivery_time')->label('Tiempo de entrega')->disabled(self::locked())->columnSpanFull(),
                    DatePicker::make('commitment_delivery_date')->label('Tiempo compromiso entrega')->native(false)->disabled(self::locked()),
                    DatePicker::make('commitment_installation_start_date')->label('Fecha inicio instalación')->native(false)->disabled(self::locked()),
                    DatePicker::make('commitment_project_date')->label('Fecha de proyecto')->native(false)->disabled(self::locked()),
                ])
                ->columns(3)
                ->columnSpanFull()
                ->compact(),
            Section::make('Cliente final')
                ->schema([
                    TextInput::make('cover_payload.final_client.name')->label('Nombre / razón social')->disabled(self::locked()),
                    TextInput::make('cover_payload.final_client.street')->label('Calle y número')->disabled(self::locked()),
                    Select::make('account_executive_user_id')
                        ->label('Ejecutivo de cuenta')
                        ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->native(false)
                        ->disabled(self::locked()),
                    TextInput::make('cover_payload.final_client.city_state')->label('Ciudad y estado')->disabled(self::locked()),
                    TextInput::make('cover_payload.final_client.postal_code')->label('C.P.')->disabled(self::locked()),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->compact(),
            Section::make('Observaciones y recomendaciones')
                ->schema([
                    RichEditor::make('observations_and_recommendations')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->disabled(self::locked()),
                ])
                ->columnSpanFull()
                ->compact(),
            Section::make('Datos fiscales y administración')
                ->schema([
                    TextInput::make('cover_payload.fiscal.payment_method')->label('Método de pago')->disabled(self::locked()),
                    TextInput::make('cover_payload.fiscal.payment_form')->label('Forma de pago')->disabled(self::locked()),
                    TextInput::make('cover_payload.fiscal.cfdi_use')->label('Uso de CFDI')->disabled(self::locked()),
                    Textarea::make('cover_payload.administration_notes')->label('Administración')->rows(4)->disabled(self::locked())->columnSpanFull(),
                ])
                ->columns(3)
                ->columnSpanFull()
                ->compact(),
        ];
    }

    protected static function locked(): Closure
    {
        return fn (?CommercialFup $record): bool => $record?->isEditingLocked() ?? false;
    }
}