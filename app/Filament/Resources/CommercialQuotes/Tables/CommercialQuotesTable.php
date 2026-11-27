<?php

namespace App\Filament\Resources\CommercialQuotes\Tables;

use App\Enums\CommercialQuoteWorkflowStep;
use App\Enums\SalaryCurrency;
use App\Models\CommercialQuote;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommercialQuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_number')
                    ->label('Folio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quoted_at')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('workflow_step')
                    ->label('Etapa')
                    ->badge()
                    ->getStateUsing(fn (CommercialQuote $record): CommercialQuoteWorkflowStep => $record->activeWorkflowStep())
                    ->formatStateUsing(fn (CommercialQuoteWorkflowStep $state): string => $state->getLabel())
                    ->color(fn (CommercialQuoteWorkflowStep $state): string => match ($state) {
                        CommercialQuoteWorkflowStep::Cancelled => 'danger',
                        CommercialQuoteWorkflowStep::ClientAuthorized => 'success',
                        CommercialQuoteWorkflowStep::InternallyAuthorized => 'success',
                        CommercialQuoteWorkflowStep::WaitingInternalAuthorization,
                        CommercialQuoteWorkflowStep::AwaitingClientAuthorization => 'warning',
                        CommercialQuoteWorkflowStep::NegotiationReview => 'info',
                        default => 'gray',
                    })
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('workflow_step', $direction)),
                TextColumn::make('presentation_currency')
                    ->label('Moneda')
                    ->badge(),
                TextColumn::make('opportunity.name')
                    ->label('Oportunidad')
                    ->toggleable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('workflow_step')
                    ->label('Etapa')
                    ->options(CommercialQuoteWorkflowStep::class)
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return;
                        }

                        $query->whereActiveWorkflowStep(
                            CommercialQuoteWorkflowStep::from($value),
                        );
                    }),
                SelectFilter::make('presentation_currency')
                    ->label('Moneda')
                    ->options(SalaryCurrency::class),
                TrashedFilter::make(),
            ])
            ->defaultSort('quoted_at', 'desc');
    }
}
