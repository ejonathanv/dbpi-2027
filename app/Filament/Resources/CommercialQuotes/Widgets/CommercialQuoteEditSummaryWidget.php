<?php

namespace App\Filament\Resources\CommercialQuotes\Widgets;

use App\Models\CommercialQuote;
use App\Support\CommercialQuoteTotalsCalculator;
use Filament\Schemas\Components\Component;
use Filament\Support\Components\Attributes\Unexposed;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class CommercialQuoteEditSummaryWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    public ?CommercialQuote $record = null;

    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = 4;

    #[Unexposed]
    public function getSectionContentComponent(): Component
    {
        return parent::getSectionContentComponent()
            ->extraAttributes([
                'class' => 'commercial-quote-edit-summary',
            ]);
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        if (! $this->record instanceof CommercialQuote) {
            return [];
        }

        $totals = CommercialQuoteTotalsCalculator::forQuote($this->record);

        return [
            Stat::make('Responsable', $totals['responsible_name'])
                ->description('Creador de la cotización')
                ->descriptionIcon(Heroicon::OutlinedUser)
                ->color('gray'),
            Stat::make('Importe cliente', '$'.number_format($totals['customer_amount'], 2, '.', ','))
                ->description('Productos, recursos y contratos')
                ->descriptionIcon(Heroicon::OutlinedCurrencyDollar)
                ->color('primary'),
            Stat::make('Margen %', number_format($totals['margin_percent'], 2, '.', ',').' %')
                ->description('Sobre importe cliente')
                ->descriptionIcon(Heroicon::OutlinedChartBar)
                ->color('success'),
            Stat::make('Margen $', '$'.number_format($totals['margin_amount'], 2, '.', ','))
                ->description('Suma de márgenes')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),
        ];
    }

    #[On('commercial-quote-margin-updated')]
    public function refreshQuoteSummary(): void
    {
        $this->cachedStats = null;
    }
}
