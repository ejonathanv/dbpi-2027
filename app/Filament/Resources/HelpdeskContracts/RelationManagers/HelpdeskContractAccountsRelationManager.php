<?php

namespace App\Filament\Resources\HelpdeskContracts\RelationManagers;

use App\Models\HelpdeskContract;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class HelpdeskContractAccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'accounts';

    protected static ?string $title = 'Cuentas asignadas';

    protected static ?string $modelLabel = 'cuenta';

    protected static ?string $pluralModelLabel = 'Cuentas';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pivot.starts_at')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),
                TextColumn::make('pivot.ends_at')
                    ->label('Fin')
                    ->date()
                    ->sortable(),
                TextColumn::make('legal_name')
                    ->label('Razón social')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('primary_email')
                    ->label('Correo')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->headerActions([
                AttachAction::make()
                    ->label('Asignar cuentas')
                    ->modalHeading('Asignar cuentas CRM')
                    ->modalSubmitActionLabel('Asignar')
                    ->preloadRecordSelect()
                    ->multiple()
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query->orderBy('name'))
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        DatePicker::make('starts_at')
                            ->label('Inicio del contrato')
                            ->required()
                            ->native(false)
                            ->default(now())
                            ->live(),
                        Placeholder::make('ends_at_preview')
                            ->label('Fin estimado (según vigencia)')
                            ->content(function (Get $get): string {
                                $start = $get('starts_at');

                                if (blank($start)) {
                                    return '—';
                                }

                                $contract = $this->getOwnerRecord();

                                if (! $contract instanceof HelpdeskContract) {
                                    return '—';
                                }

                                return $contract->computeEndsAt($start)->format('d/m/Y');
                            }),
                    ])
                    ->using(function (array $data, Model|iterable|null $record, BelongsToMany $relationship): void {
                        /** @var HelpdeskContract $contract */
                        $contract = $this->getOwnerRecord();
                        $startsAt = Carbon::parse($data['starts_at'])->startOfDay();
                        $pivot = [
                            'starts_at' => $startsAt->toDateString(),
                            'ends_at' => $contract->computeEndsAt($startsAt)->toDateString(),
                        ];

                        $recordIds = $data['recordId'] ?? null;

                        if (is_array($recordIds)) {
                            foreach ($recordIds as $recordId) {
                                $relationship->attach($recordId, $pivot);
                            }

                            return;
                        }

                        $relationship->attach($record ?? $recordIds, $pivot);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar vigencia')
                    ->modalHeading('Vigencia del contrato en cuenta')
                    ->schema([
                        DatePicker::make('starts_at')
                            ->label('Inicio del contrato')
                            ->required()
                            ->native(false)
                            ->live(),
                        Placeholder::make('ends_at_preview')
                            ->label('Fin (según vigencia del contrato)')
                            ->content(function (Get $get): string {
                                $start = $get('starts_at');

                                if (blank($start)) {
                                    return '—';
                                }

                                $contract = $this->getOwnerRecord();

                                if (! $contract instanceof HelpdeskContract) {
                                    return '—';
                                }

                                return $contract->computeEndsAt($start)->format('d/m/Y');
                            }),
                    ])
                    ->fillForm(fn (Model $record): array => [
                        'starts_at' => $record->pivot?->starts_at,
                    ])
                    ->using(function (Model $record, array $data): void {
                        /** @var HelpdeskContract $contract */
                        $contract = $this->getOwnerRecord();
                        $startsAt = Carbon::parse($data['starts_at'])->startOfDay();

                        $contract->accounts()->updateExistingPivot($record->getKey(), [
                            'starts_at' => $startsAt->toDateString(),
                            'ends_at' => $contract->computeEndsAt($startsAt)->toDateString(),
                        ]);
                    }),
                DetachAction::make()
                    ->label('Quitar'),
            ])
            ->toolbarActions([
                DetachBulkAction::make()
                    ->label('Quitar seleccionadas'),
            ]);
    }
}
