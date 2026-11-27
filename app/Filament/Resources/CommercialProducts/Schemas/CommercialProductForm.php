<?php

namespace App\Filament\Resources\CommercialProducts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommercialProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Datos generales')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del producto')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('part_number')
                            ->label('No. de parte')
                            ->maxLength(100),
                        TextInput::make('sat_code')
                            ->label('Código SAT')
                            ->maxLength(50),
                        Select::make('commercial_product_category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nueva categoría')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->native(false),
                        Select::make('commercial_product_type_id')
                            ->label('Tipo')
                            ->relationship('type', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nuevo tipo')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->native(false),
                        TextInput::make('brand')
                            ->label('Marca')
                            ->maxLength(255),
                        TextInput::make('model')
                            ->label('Modelo')
                            ->maxLength(255),
                    ]),
                Section::make('Especificaciones')
                    ->schema([
                        RichEditor::make('specifications')
                            ->label('Especificaciones')
                            ->columnSpanFull(),
                    ]),
                Section::make('Imágenes')
                    ->description('Hasta 3 fotos del producto.')
                    ->schema([
                        FileUpload::make('image_paths')
                            ->label('Fotos')
                            ->image()
                            ->multiple()
                            ->maxFiles(3)
                            ->reorderable()
                            ->disk('public')
                            ->directory('commercial/products')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
                Section::make('Estado')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),
            ]);
    }
}
