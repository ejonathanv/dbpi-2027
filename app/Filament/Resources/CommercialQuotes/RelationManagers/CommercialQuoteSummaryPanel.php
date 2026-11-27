<?php

namespace App\Filament\Resources\CommercialQuotes\RelationManagers;

use App\Support\CommercialQuotePreviewBuilder;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Livewire\Attributes\On;

class CommercialQuoteSummaryPanel extends RelationManager
{
    protected static string $relationship = 'groups';

    protected static ?string $title = 'Resumen';

    /**
     * @var view-string
     */
    protected string $view = 'filament.resources.commercial-quotes.summary-panel';

    public function table(Table $table): Table
    {
        return $table->columns([])->paginated(false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPreviewProperty(): array
    {
        return CommercialQuotePreviewBuilder::forQuote(
            $this->getOwnerRecord()->load(['account', 'contact']),
        );
    }

    #[On('commercial-quote-margin-updated')]
    public function refreshSummaryPreview(): void
    {
        $this->getOwnerRecord()->refresh();
    }
}
