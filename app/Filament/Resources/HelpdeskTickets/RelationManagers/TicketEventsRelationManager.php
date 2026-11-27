<?php

namespace App\Filament\Resources\HelpdeskTickets\RelationManagers;

use App\Enums\HelpdeskAttentionType;
use App\Enums\HelpdeskEventVisibility;
use App\Models\HelpdeskTicketEvent;
use App\Support\HelpdeskTimeOptions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static ?string $title = 'Eventos';

    protected static ?string $modelLabel = 'evento';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Registro del evento')
                    ->columns(2)
                    ->schema([
                        Textarea::make('description')
                            ->label('Comentario o descripción')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Select::make('work_hours')
                            ->label('Horas (tiempo invertido)')
                            ->options(HelpdeskTimeOptions::workHours())
                            ->default(1)
                            ->required()
                            ->live()
                            ->native(false),
                        Select::make('work_minutes')
                            ->label('Minutos (tiempo invertido)')
                            ->options(HelpdeskTimeOptions::minutes())
                            ->default(0)
                            ->required()
                            ->disabled(fn (Get $get): bool => (int) $get('work_hours') === 10)
                            ->dehydrated()
                            ->native(false),
                        Select::make('attention_type')
                            ->label('Tipo de atención')
                            ->options(HelpdeskAttentionType::class)
                            ->default(HelpdeskAttentionType::Remota)
                            ->required()
                            ->live()
                            ->native(false)
                            ->columnSpanFull(),
                        Select::make('travel_hours')
                            ->label('Horas (traslado)')
                            ->options(HelpdeskTimeOptions::travelHours())
                            ->default(0)
                            ->visible(fn (Get $get): bool => $get('attention_type') === HelpdeskAttentionType::EnSitio->value)
                            ->required(fn (Get $get): bool => $get('attention_type') === HelpdeskAttentionType::EnSitio->value)
                            ->live()
                            ->native(false),
                        Select::make('travel_minutes')
                            ->label('Minutos (traslado)')
                            ->options(HelpdeskTimeOptions::minutes())
                            ->default(0)
                            ->visible(fn (Get $get): bool => $get('attention_type') === HelpdeskAttentionType::EnSitio->value)
                            ->required(fn (Get $get): bool => $get('attention_type') === HelpdeskAttentionType::EnSitio->value)
                            ->disabled(fn (Get $get): bool => (int) $get('travel_hours') === 24)
                            ->dehydrated()
                            ->native(false),
                        Select::make('visibility')
                            ->label('Publicar como')
                            ->options(HelpdeskEventVisibility::class)
                            ->default(HelpdeskEventVisibility::Privado)
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),
                        FileUpload::make('attachment_paths')
                            ->label('Adjuntos (imagen o PDF, máx. 3)')
                            ->multiple()
                            ->maxFiles(3)
                            ->disk('public')
                            ->directory('helpdesk/event-attachments')
                            ->visibility('public')
                            ->acceptedFileTypes([
                                'image/*',
                                'application/pdf',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('workTimeLabel')
                    ->label('Tiempo')
                    ->state(fn (HelpdeskTicketEvent $record): string => $record->workTimeLabel()),
                TextColumn::make('attention_type')
                    ->label('Atención'),
                TextColumn::make('visibility')
                    ->label('Visibilidad')
                    ->badge(),
                TextColumn::make('creator.name')
                    ->label('Registró')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar evento')
                    ->mutateFormDataUsing(function (array $data): array {
                        if ((int) ($data['work_hours'] ?? 0) === 10) {
                            $data['work_minutes'] = 0;
                        }

                        if (($data['attention_type'] ?? null) !== HelpdeskAttentionType::EnSitio->value) {
                            $data['travel_hours'] = null;
                            $data['travel_minutes'] = null;
                        } elseif ((int) ($data['travel_hours'] ?? 0) === 24) {
                            $data['travel_minutes'] = 0;
                        }

                        return $data;
                    })
                    ->using(function (array $data, TicketEventsRelationManager $livewire): HelpdeskTicketEvent {
                        $paths = $data['attachment_paths'] ?? [];
                        unset($data['attachment_paths']);

                        /** @var HelpdeskTicketEvent $event */
                        $event = $livewire->getRelationship()->create($data);

                        foreach ($paths as $path) {
                            $event->attachments()->create([
                                'path' => $path,
                                'original_name' => basename((string) $path),
                            ]);
                        }

                        return $event;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ]);
    }
}
