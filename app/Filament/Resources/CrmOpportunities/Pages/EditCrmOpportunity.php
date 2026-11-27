<?php

namespace App\Filament\Resources\CrmOpportunities\Pages;

use App\Enums\AppModule;
use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use App\Filament\Resources\CrmOpportunities\CrmOpportunityResource;
use App\Models\CrmOpportunity;
use App\Models\User;
use App\Support\CommercialQuoteFromOpportunity;
use App\Support\ModuleAccess;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCrmOpportunity extends EditRecord
{
    protected static string $resource = CrmOpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateQuote')
                ->label('Generar cotización')
                ->icon(Heroicon::OutlinedDocumentCurrencyDollar)
                ->visible(fn (): bool => $this->canGenerateQuote())
                ->action(function (CrmOpportunity $record): void {
                    $user = auth()->user();
                    $quote = CommercialQuoteFromOpportunity::create(
                        $record,
                        $user instanceof User ? $user : null,
                    );

                    $this->redirect(CommercialQuoteResource::getUrl('edit', ['record' => $quote]));
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function canGenerateQuote(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && ModuleAccess::canEditModule($user, AppModule::Crm)
            && ModuleAccess::canEditModule($user, AppModule::Comercial);
    }
}
