<?php

namespace App\Filament\Resources\CrmOpportunities\Schemas;

use App\Enums\CrmOpportunityStage;
use App\Models\CrmAccount;
use App\Models\CrmContact;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CrmOpportunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('crm_account_id')
                    ->label('Cuenta')
                    ->options(fn (): array => CrmAccount::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->native(false)
                    ->columnSpanFull(),
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
                Select::make('assigned_user_id')
                    ->label('Responsable')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->native(false),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('stage')
                    ->label('Etapa')
                    ->options(CrmOpportunityStage::class)
                    ->required()
                    ->native(false),
                TextInput::make('amount')
                    ->label('Monto')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('currency')
                    ->label('Moneda')
                    ->default('MXN')
                    ->maxLength(3),
                DatePicker::make('expected_close_date')
                    ->label('Cierre estimado')
                    ->native(false),
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
