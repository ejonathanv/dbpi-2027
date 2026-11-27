<?php

namespace App\Filament\Resources\CommercialFups\Schemas\Tabs;

use App\Models\CommercialFup;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class CommercialFupCoverTab
{
    /** @return array<int, mixed> */
    public static function schema(): array
    {
        return [
            Section::make('Datos de la FUP')
                ->columns(3)
                ->schema([
                    TextInput::make('fup_number')
                        ->label('Número FUP')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('client_po_number')
                        ->label('No. orden del cliente')
                        ->maxLength(255)
                        ->disabled(fn (?CommercialFup $record): bool => $record?->isEditingLocked() ?? false),
                    DatePicker::make('fup_date')
                        ->label('Fecha FUP')
                        ->required()
                        ->native(false)
                        ->disabled(fn (?CommercialFup $record): bool => $record?->isEditingLocked() ?? false),
                    Select::make('account_executive_user_id')
                        ->label('Ejecutivo de cuenta')
                        ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->native(false)
                        ->disabled(fn (?CommercialFup $record): bool => $record?->isEditingLocked() ?? false)
                        ->columnSpanFull(),
                ]),
            Section::make('Fechas de compromiso')
                ->columns(3)
                ->schema([
                    DatePicker::make('commitment_delivery_date')
                        ->label('Fecha compromiso entrega')
                        ->native(false)
                        ->disabled(fn (?CommercialFup $record): bool => $record?->isEditingLocked() ?? false),
                    DatePicker::make('commitment_installation_start_date')
                        ->label('Inicio instalación')
                        ->native(false)
                        ->disabled(fn (?CommercialFup $record): bool => $record?->isEditingLocked() ?? false),
                    DatePicker::make('commitment_project_date')
                        ->label('Fecha de proyecto')
                        ->native(false)
                        ->disabled(fn (?CommercialFup $record): bool => $record?->isEditingLocked() ?? false),
                ]),
            Section::make('Observaciones y recomendaciones')
                ->schema([
                    RichEditor::make('observations_and_recommendations')
                        ->label('')
                        ->hiddenLabel()
                        ->columnSpanFull(),
                ]),
        ];
    }
}