<?php

namespace App\Filament\Resources\CommercialFups\Pages;

use App\Filament\Resources\CommercialFups\CommercialFupResource;
use Filament\Resources\Pages\ListRecords;

class ListCommercialFups extends ListRecords
{
    protected static string $resource = CommercialFupResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
