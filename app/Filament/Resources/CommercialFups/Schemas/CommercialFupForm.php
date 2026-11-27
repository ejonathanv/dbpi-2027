<?php

namespace App\Filament\Resources\CommercialFups\Schemas;

use App\Models\CommercialQuote;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommercialFupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('FUP')->schema([
                TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                Select::make('commercial_quote_id')
                    ->label('Cotización')
                    ->options(fn (): array => CommercialQuote::query()->orderByDesc('id')->pluck('quote_number', 'id')->all())
                    ->searchable()
                    ->native(false),
            ]),
        ]);
    }
}
