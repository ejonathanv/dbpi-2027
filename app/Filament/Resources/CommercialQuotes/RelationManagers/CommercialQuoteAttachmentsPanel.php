<?php

namespace App\Filament\Resources\CommercialQuotes\RelationManagers;

use App\Enums\CommercialQuoteAttachmentType;
use App\Models\CommercialQuoteAttachment;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CommercialQuoteAttachmentsPanel extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = 'Archivos adjuntos';

    public ?string $attachmentTypeFilter = null;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Título')->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('uploader.name')->label('Subido por')->toggleable(),
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('file_path')
                    ->label('Archivo')
                    ->formatStateUsing(fn (string $state): string => basename($state))
                    ->url(fn (CommercialQuoteAttachment $record): string => $record->downloadUrl(), shouldOpenInNewTab: true)
                    ->color('primary'),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query): Builder {
                if (filled($this->attachmentTypeFilter)) {
                    $query->where('type', $this->attachmentTypeFilter);
                }

                return $query;
            })
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar archivo')
                    ->modalHeading('Nuevo archivo adjunto')
                    ->createAnother(false)
                    ->mutateFormDataUsing(function (array $data): array {
                        $user = Filament::auth()->user();
                        $data['uploaded_by_user_id'] = $user?->getKey();

                        return $data;
                    }),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Título')->required()->maxLength(255),
            Select::make('type')
                ->label('Tipo de archivo')
                ->options(CommercialQuoteAttachmentType::class)
                ->default($this->attachmentTypeFilter ?? CommercialQuoteAttachmentType::Other->value)
                ->required()
                ->native(false),
            FileUpload::make('file_path')
                ->label('Archivo')
                ->directory('commercial-quotes/attachments')
                ->disk('public')
                ->required()
                ->downloadable()
                ->openable(),
        ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }
}