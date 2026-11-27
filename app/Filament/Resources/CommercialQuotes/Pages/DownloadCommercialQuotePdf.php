<?php

namespace App\Filament\Resources\CommercialQuotes\Pages;

use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use App\Support\CommercialQuotePdfExporter;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Gate;

class DownloadCommercialQuotePdf extends Page
{
    use InteractsWithRecord;

    protected static string $resource = CommercialQuoteResource::class;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.resources.commercial-quotes.pdf-download-blank';

    public function mount(int|string $record, string $variant): void
    {
        $this->record = $this->resolveRecord($record);

        Gate::authorize('view', $this->getRecord());

        throw new HttpResponseException(
            CommercialQuotePdfExporter::download($this->getRecord(), $variant)
        );
    }
}