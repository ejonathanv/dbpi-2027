<?php

namespace App\Filament\Resources\CommercialFups\RelationManagers;

use App\Support\CommercialFupContractSync;
use Filament\Actions\Action;
use App\Models\CommercialFupContractItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CommercialFupContractsPanel extends RelationManager
{
    protected static string $relationship = 'contractItems';

    protected static ?string $title = 'Contratos';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract.name')->label('Contrato')->searchable(),
                TextColumn::make('lineItem.id')
                    ->label('Partida')
                    ->formatStateUsing(fn (CommercialFupContractItem $record): string => (string) ($record->lineItem?->contract?->name ?? '—')),
                TextColumn::make('starts_at')->label('Inicio')->date('d/m/Y'),
                TextColumn::make('ends_at')->label('Fin')->date('d/m/Y'),
            ])
            ->recordActions([
                Action::make('editDates')
                    ->label('Editar vigencia')
                    ->icon('heroicon-o-calendar')
                    ->fillForm(fn (Model $record): array => [
                        'starts_at' => $record->starts_at,
                    ])
                    ->schema([
                        DatePicker::make('starts_at')
                            ->label('Inicio del contrato')
                            ->required()
                            ->native(false)
                            ->live(),
                        Placeholder::make('ends_preview')
                            ->label('Fin estimado')
                            ->content(function (Get $get, Model $record): string {
                                $start = $get('starts_at');

                                if (blank($start)) {
                                    return '—';
                                }

                                return $record->contract?->computeEndsAt($start)->format('d/m/Y') ?? '—';
                            }),
                    ])
                    ->action(function (Model $record, array $data): void {
                        $record->update(['starts_at' => $data['starts_at']]);
                        CommercialFupContractSync::syncToHelpdesk($record);
                    }),
            ])
            ->emptyStateHeading('Sin contratos en la cotización')
            ->emptyStateDescription('Agregue contratos en la cotización para gestionarlos aquí.');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('id')->disabled(),
        ]);
    }
}