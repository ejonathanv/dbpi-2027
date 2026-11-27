<?php

namespace App\Filament\Resources\HelpdeskCategories\Pages;

use App\Filament\Resources\HelpdeskCategories\HelpdeskCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHelpdeskCategories extends ListRecords
{
    protected static string $resource = HelpdeskCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
