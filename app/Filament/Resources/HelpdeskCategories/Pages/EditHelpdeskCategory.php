<?php

namespace App\Filament\Resources\HelpdeskCategories\Pages;

use App\Filament\Resources\HelpdeskCategories\HelpdeskCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHelpdeskCategory extends EditRecord
{
    protected static string $resource = HelpdeskCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
