<?php

namespace App\Filament\Resources\CommercialQuotes\Pages;

use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use App\Filament\Resources\CommercialQuotes\Widgets\CommercialQuoteEditSummaryWidget;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCommercialQuote extends EditRecord
{
    protected static string $resource = CommercialQuoteResource::class;

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            CommercialQuoteEditSummaryWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 4;
    }

    /**
     * @return array<string, mixed>
     */
    public function getWidgetData(): array
    {
        return [
            'record' => $this->getRecord(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->dispatch('commercial-quote-margin-updated');
    }
}
