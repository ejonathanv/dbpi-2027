<?php

namespace App\Filament\Resources\CrmAccounts\Tables;

use App\Enums\CrmAccountType;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CrmAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn (): string => 'https://ui-avatars.com/api/?background=e5e7eb&color=374151&name=C'),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account_type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),
                TextColumn::make('legal_name')
                    ->label('Razón social')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('primary_email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->toggleable(),
                TextColumn::make('industry')
                    ->label('Industria')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('account_type')
                    ->label('Tipo')
                    ->options(CrmAccountType::class),
            ]);
    }
}
