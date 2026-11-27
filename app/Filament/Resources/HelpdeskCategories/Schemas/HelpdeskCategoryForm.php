<?php

namespace App\Filament\Resources\HelpdeskCategories\Schemas;

use App\Models\HelpdeskCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class HelpdeskCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Categoría')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('parent_id')
                            ->label('Categoría padre')
                            ->placeholder('Nivel 1 (sin padre)')
                            ->searchable()
                            ->native(false)
                            ->options(function (?HelpdeskCategory $record): array {
                                return HelpdeskCategory::query()
                                    ->where('depth', '<', 3)
                                    ->when(
                                        $record,
                                        fn (Builder $query): Builder => $query->whereKeyNot($record->getKey()),
                                    )
                                    ->orderBy('depth')
                                    ->orderBy('sort_order')
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (HelpdeskCategory $category): array => [
                                        $category->getKey() => $category->fullPath(),
                                    ])
                                    ->all();
                            })
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                    ]),
            ]);
    }
}
